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


def ler_cartao(caminho_imagem):
    """
    Detecta marcações em um cartão-resposta.
    Retorna dict {questao: 'A'|'B'|'C'|'D'|'E'|''} para 45 questões.
    """
    img = cv2.imread(caminho_imagem)
    if img is None:
        erro(f"Não foi possível abrir a imagem: {caminho_imagem}")

    gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
    blur = cv2.GaussianBlur(gray, (5, 5), 0)
    _, thresh = cv2.threshold(blur, 0, 255, cv2.THRESH_BINARY_INV + cv2.THRESH_OTSU)

    contornos, _ = cv2.findContours(thresh, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)

    bolinhas = []
    for c in contornos:
        area = cv2.contourArea(c)
        if area < 80 or area > 3000:
            continue
        perimetro = cv2.arcLength(c, True)
        if perimetro == 0:
            continue
        circularidade = 4 * np.pi * (area / (perimetro * perimetro))
        if circularidade < 0.6:
            continue
        (x, y), raio = cv2.minEnclosingCircle(c)
        if raio < 4 or raio > 25:
            continue
        bolinhas.append({'x': float(x), 'y': float(y), 'r': float(raio), 'area': float(area)})

    if len(bolinhas) < 225:
        erro(f"Detectei apenas {len(bolinhas)} bolinhas. Esperado >= 225 (45x5). Ajuste o enquadramento/iluminação.")

    bolinhas.sort(key=lambda b: (b['y'], b['x']))

    # agrupa em linhas
    linhas = []
    linha_atual = []
    y_ref = bolinhas[0]['y']
    tolerancia_y = bolinhas[0]['r'] * 2

    for b in bolinhas:
        if abs(b['y'] - y_ref) <= tolerancia_y:
            linha_atual.append(b)
        else:
            linhas.append(linha_atual)
            linha_atual = [b]
            y_ref = b['y']
    if linha_atual:
        linhas.append(linha_atual)

    # mantém apenas linhas com pelo menos 3 bolinhas
    linhas = [l for l in linhas if len(l) >= 3]

    if len(linhas) < 45:
        erro(f"Detectei {len(linhas)} linhas. Esperado 45. Ajuste o enquadramento.")

    # pega as 45 primeiras linhas válidas
    linhas = linhas[:45]

    respostas = {}
    for idx, linha in enumerate(linhas, start=1):
        linha.sort(key=lambda b: b['x'])
        # pega até 5 bolinhas dessa linha (A-E)
        alternativas = linha[:5]
        if len(alternativas) < 5:
            respostas[idx] = ''
            continue

        # mede densidade de preenchimento de cada bolinha
        densidades = []
        for b in alternativas:
            x, y, r = int(b['x']), int(b['y']), int(b['r'])
            x1, y1 = max(0, x - r), max(0, y - r)
            x2, y2 = min(gray.shape[1], x + r), min(gray.shape[0], y + r)
            roi = gray[y1:y2, x1:x2]
            if roi.size == 0:
                densidades.append(0)
                continue
            densidades.append(float(np.mean(roi)))

        # a bolinha marcada = MENOR média de cinza (mais escura)
        media_geral = np.mean(densidades)
        marcada_idx = int(np.argmin(densidades))
        # só considera marcada se for significativamente mais escura
        if densidades[marcada_idx] < media_geral - 15:
            letras = ['A', 'B', 'C', 'D', 'E']
            respostas[idx] = letras[marcada_idx]
        else:
            respostas[idx] = ''

    return respostas


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument('--aluno', required=True)
    parser.add_argument('--gabarito', required=True)
    parser.add_argument('--nome', required=True)
    parser.add_argument('--matricula', required=True)
    parser.add_argument('--saida', required=True)
    args = parser.parse_args()

    respostas_aluno = ler_cartao(args.aluno)
    respostas_gabarito = ler_cartao(args.gabarito)

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
        'nome': args.nome,
        'matricula': args.matricula,
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