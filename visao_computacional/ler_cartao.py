import argparse
import json
import sys

def erro(msg):
    print("ERRO: " + msg, file=sys.stderr)
    sys.exit(1)

try:
    import cv2
    import numpy as np
except ImportError:
    erro("Instale as dependencias: pip install -r requirements.txt")


def ler_cartao(caminho_imagem, debug=False):
    img = cv2.imread(caminho_imagem)
    if img is None:
        erro(f"Não foi possível abrir a imagem: {caminho_imagem}")

    h, w = img.shape[:2]
    if max(h, w) > 2500:
        escala = 2500 / max(h, w)
        img = cv2.resize(img, (int(w * escala), int(h * escala)))
        h, w = img.shape[:2]

    gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
    blur = cv2.GaussianBlur(gray, (5, 5), 0)
    _, thresh = cv2.threshold(blur, 0, 255, cv2.THRESH_BINARY_INV + cv2.THRESH_OTSU)

    contornos, _ = cv2.findContours(thresh, cv2.RETR_LIST, cv2.CHAIN_APPROX_SIMPLE)

    bolinhas = []
    for c in contornos:
        area = cv2.contourArea(c)
        if area < 100 or area > 3000:
            continue
        perimetro = cv2.arcLength(c, True)
        if perimetro == 0:
            continue
        circularidade = 4 * np.pi * (area / (perimetro * perimetro))
        if circularidade < 0.7:
            continue
        (x, y), raio = cv2.minEnclosingCircle(c)
        if raio < 7 or raio > 30:
            continue
        bolinhas.append({'x': float(x), 'y': float(y), 'r': float(raio), 'area': float(area)})

    if debug:
        print(f"DIAG: Bolinhas brutas={len(bolinhas)}", file=sys.stderr)

    if len(bolinhas) < 180:
        erro(f"Detectei apenas {len(bolinhas)} bolinhas. A imagem está nítida?")

    # Remove a "Versão" se houver. A Versão fica no topo e tem apenas 4 bolinhas.
    # As questões têm 12 bolinhas por linha. Vamos ordenar por Y e agrupar em linhas.
    bolinhas.sort(key=lambda b: b['y'])
    
    linhas = []
    linha_atual = []
    y_ref = bolinhas[0]['y']
    tolerancia_y = bolinhas[0]['r'] * 1.5

    for b in bolinhas:
        if abs(b['y'] - y_ref) <= tolerancia_y:
            linha_atual.append(b)
        else:
            linhas.append(linha_atual)
            linha_atual = [b]
            y_ref = b['y']
    if linha_atual:
        linhas.append(linha_atual)

    # Filtrar as linhas que realmente são de questões (aquelas que têm >= 10 bolinhas)
    linhas_questoes = [l for l in linhas if len(l) >= 10]
    
    if debug:
        print(f"DIAG: Linhas de questões encontradas={len(linhas_questoes)}", file=sys.stderr)

    if len(linhas_questoes) < 15:
        erro(f"Detectei apenas {len(linhas_questoes)} linhas de questões. Esperado 15.")

    # Pega as 15 linhas mais cheias e ordena por Y
    linhas_questoes.sort(key=lambda l: len(l), reverse=True)
    top_15_linhas = linhas_questoes[:15]
    top_15_linhas.sort(key=lambda l: l[0]['y'])

    respostas = {}

    for idx_linha, linha in enumerate(top_15_linhas):
        # Ordena as bolinhas da linha por X (da esquerda para a direita)
        linha.sort(key=lambda b: b['x'])
        
        # Filtra para remover possíveis números (ex: "01") usando o raio mediano
        raios = [b['r'] for b in linha]
        raio_mediano = np.median(raios)
        linha_filtrada = [b for b in linha if abs(b['r'] - raio_mediano) < raio_mediano * 0.3]
        
        if len(linha_filtrada) < 12:
            linha_filtrada = linha # fallback
            
        # Como o cartão tem 3 colunas, a linha deve ter 12 bolinhas (3x4)
        # Divide em 3 grupos de 4
        if len(linha_filtrada) >= 12:
            grupos = [linha_filtrada[0:4], linha_filtrada[4:8], linha_filtrada[8:12]]
        else:
            # Se não achou 12, tenta dividir igualmente
            grupos = np.array_split(linha_filtrada, 3)

        for col_idx, grupo in enumerate(grupos):
            q_num = (col_idx * 15) + idx_linha + 1
            
            if len(grupo) < 4:
                respostas[q_num] = ''
                continue
                
            grupo_ordenado = sorted(grupo, key=lambda b: b['x'])
            
            densidades = []
            for b in grupo_ordenado:
                x, y, r = int(b['x']), int(b['y']), int(b['r'])
                # Pega um recorte no centro exato da bolinha
                roi = gray[y-3:y+4, x-3:x+4]
                if roi.size == 0:
                    densidades.append(255)
                    continue
                densidades.append(float(np.mean(roi)))

            media_geral = np.mean(densidades)
            marcada_idx = int(np.argmin(densidades))

            # Se a bolinha for escura o suficiente, consideramos marcada
            if densidades[marcada_idx] < 128:
                letras = ['A', 'B', 'C', 'D']
                respostas[q_num] = letras[marcada_idx]
            else:
                respostas[q_num] = ''

    return respostas


def extrair_nome_matricula(caminho_imagem):
    return '', ''


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument('--aluno', required=True)
    parser.add_argument('--gabarito')
    parser.add_argument('--gabarito-json')
    parser.add_argument('--saida', required=True)
    parser.add_argument('--debug', action='store_true')
    args = parser.parse_args()

    respostas_aluno = ler_cartao(args.aluno, debug=args.debug)

    if args.gabarito_json:
        with open(args.gabarito_json, 'r', encoding='utf-8') as f:
            respostas_gabarito_raw = json.load(f)
        respostas_gabarito = {int(k): v for k, v in respostas_gabarito_raw.items()}
    else:
        respostas_gabarito = ler_cartao(args.gabarito, debug=args.debug)

    nome, matricula = extrair_nome_matricula(args.aluno)

    acertos = 0
    detalhes = {}
    for q in range(1, 46):
        g = respostas_gabarito.get(q, '')
        a = respostas_aluno.get(q, '')
        acertou = (g != '' and g == a)
        if acertou:
            acertos += 1
        detalhes[q] = {
            'gabarito': g,
            'aluno': a,
            'acertou': acertou
        }

    resultado = {
        'nome': nome,
        'matricula': matricula,
        'acertos': acertos,
        'erros': 45 - acertos,
        'nota': round((acertos / 45) * 10, 2),
        'percentual': round((acertos / 45) * 100, 2),
        'detalhes': detalhes
    }

    with open(args.saida, 'w', encoding='utf-8') as f:
        json.dump(resultado, f, ensure_ascii=False, indent=2)

    print("OK")


if __name__ == '__main__':
    main()