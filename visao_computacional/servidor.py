import json
import sys
import tempfile
from http.server import BaseHTTPRequestHandler, HTTPServer
from socketserver import ThreadingMixIn

try:
    import cv2
    import numpy as np
except ImportError:
    print("Instale: pip install opencv-python numpy")
    sys.exit(1)


def detectar_bolinhas(img):
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
        if area < 80 or area > 2500:
            continue
        perimetro = cv2.arcLength(c, True)
        if perimetro == 0:
            continue
        circularidade = 4 * np.pi * (area / (perimetro * perimetro))
        if circularidade < 0.78:  # mais rígido: elimina quadrados pretos
            continue
        (x, y), raio = cv2.minEnclosingCircle(c)
        if raio < 6 or raio > 25:
            continue
        # verifica se é contorno fechado (bolinha desenhada)
        x_, y_, w_, h_ = cv2.boundingRect(c)
        aspecto = w_ / max(1, h_)
        if aspecto < 0.75 or aspecto > 1.25:
            continue
        bolinhas.append({'x': float(x), 'y': float(y), 'r': float(raio), 'area': float(area)})

    return bolinhas, gray


def agrupar_linhas(bolinhas, tolerancia_fator=0.6):
    """Agrupa bolinhas por linha (mesmo Y aproximado)."""
    if not bolinhas:
        return []
    bolinhas = sorted(bolinhas, key=lambda b: b['y'])
    raio_med = float(np.median([b['r'] for b in bolinhas]))
    tol = raio_med * tolerancia_fator * 2

    linhas = []
    linha_atual = [bolinhas[0]]
    soma_y = bolinhas[0]['y']

    for b in bolinhas[1:]:
        if abs(b['y'] - (soma_y / len(linha_atual))) <= tol:
            linha_atual.append(b)
            soma_y += b['y']
        else:
            linhas.append(linha_atual)
            linha_atual = [b]
            soma_y = b['y']
    linhas.append(linha_atual)
    return linhas


def ler_cartao(img):
    bolinhas, gray = detectar_bolinhas(img)

    if len(bolinhas) < 180:
        raise ValueError(f"Detectei apenas {len(bolinhas)} bolinhas. Esperado pelo menos 180.")

    linhas = agrupar_linhas(bolinhas)

    # Filtra linhas que têm pelo menos 8 bolinhas (linhas reais de questões têm 12: 3 colunas × 4)
    linhas_questoes = [l for l in linhas if len(l) >= 8]

    if len(linhas_questoes) < 15:
        raise ValueError(
            f"Detectei {len(linhas_questoes)} linhas de questões. Esperado 15. "
            f"Corte a imagem só na área do gabarito."
        )

    # Pega as 15 linhas mais "cheias" (mais bolinhas) e reordena por Y
    linhas_questoes.sort(key=lambda l: len(l), reverse=True)
    top_15 = linhas_questoes[:15]
    top_15.sort(key=lambda l: np.mean([b['y'] for b in l]))

    # Cada linha deve ter 12 bolinhas: 3 grupos de 4 (colunas)
    # Separar por X em 3 colunas usando os centros
    respostas = {}

    for idx_linha, linha in enumerate(top_15):
        linha = sorted(linha, key=lambda b: b['x'])

        # Descarta outliers pelo raio
        raios = [b['r'] for b in linha]
        raio_med = float(np.median(raios))
        linha = [b for b in linha if abs(b['r'] - raio_med) < raio_med * 0.35]

        if len(linha) < 12:
            # tenta completar ou marcar tudo em branco
            for col in range(3):
                q = (col * 15) + idx_linha + 1
                respostas[q] = ''
            continue

        # Se tiver mais de 12, pega as 12 melhores (mais próximas do raio mediano)
        if len(linha) > 12:
            linha.sort(key=lambda b: abs(b['r'] - raio_med))
            linha = linha[:12]
            linha.sort(key=lambda b: b['x'])

        # Divide em 3 colunas de 4
        for col in range(3):
            grupo = linha[col * 4:(col + 1) * 4]
            q = (col * 15) + idx_linha + 1

            if len(grupo) < 4:
                respostas[q] = ''
                continue

            grupo = sorted(grupo, key=lambda b: b['x'])

            # Mede densidade no centro de cada bolinha (preto = marcado)
            densidades = []
            for b in grupo:
                x, y, r = int(b['x']), int(b['y']), max(2, int(b['r'] * 0.5))
                roi = gray[max(0, y - r):y + r + 1, max(0, x - r):x + r + 1]
                if roi.size == 0:
                    densidades.append(255.0)
                else:
                    densidades.append(float(np.mean(roi)))

            # A bolinha marcada é a mais escura (menor média)
            idx_marcada = int(np.argmin(densidades))
            dens_min = densidades[idx_marcada]

            # Precisa ser bem mais escura que as outras para evitar falso positivo
            outras = [d for i, d in enumerate(densidades) if i != idx_marcada]
            dens_outras_media = float(np.mean(outras)) if outras else 255.0

            if dens_min < 110 and (dens_outras_media - dens_min) > 40:
                respostas[q] = ['A', 'B', 'C', 'D'][idx_marcada]
            else:
                respostas[q] = ''

    return respostas


class Handler(BaseHTTPRequestHandler):
    def _cors(self):
        self.send_header('Access-Control-Allow-Origin', '*')
        self.send_header('Access-Control-Allow-Methods', 'POST, OPTIONS')
        self.send_header('Access-Control-Allow-Headers', 'Content-Type')

    def do_OPTIONS(self):
        self.send_response(200)
        self._cors()
        self.end_headers()

    def do_POST(self):
        try:
            tamanho = int(self.headers.get('Content-Length', 0))
            corpo = self.rfile.read(tamanho)

            inicio = -1
            fim = len(corpo)
            jpg = corpo.find(b'\xff\xd8\xff')
            png = corpo.find(b'\x89PNG')

            if jpg != -1:
                inicio = jpg
                ultimo = corpo.rfind(b'\xff\xd9')
                if ultimo != -1:
                    fim = ultimo + 2
            elif png != -1:
                inicio = png
                ultimo = corpo.rfind(b'IEND')
                if ultimo != -1:
                    fim = ultimo + 8

            if inicio == -1:
                self._responder(400, {'ok': False, 'erro': 'Nenhuma imagem encontrada no corpo.'})
                return

            dados_img = corpo[inicio:fim]
            arr = np.frombuffer(dados_img, dtype=np.uint8)
            img = cv2.imdecode(arr, cv2.IMREAD_COLOR)

            if img is None:
                self._responder(400, {'ok': False, 'erro': 'Não foi possível decodificar a imagem.'})
                return

            respostas = ler_cartao(img)
            respostas_str = {str(k): v for k, v in respostas.items()}
            self._responder(200, {'ok': True, 'respostas': respostas_str})

        except Exception as e:
            self._responder(500, {'ok': False, 'erro': str(e)})

    def _responder(self, code, dados):
        body = json.dumps(dados, ensure_ascii=False).encode('utf-8')
        self.send_response(code)
        self._cors()
        self.send_header('Content-Type', 'application/json; charset=utf-8')
        self.send_header('Content-Length', str(len(body)))
        self.end_headers()
        self.wfile.write(body)

    def log_message(self, fmt, *args):
        print("[servidor] " + fmt % args)


class ServidorThread(ThreadingMixIn, HTTPServer):
    daemon_threads = True


if __name__ == '__main__':
    porta = 5000
    print(f"Servidor de leitura rodando em http://localhost:{porta}")
    print("Deixe esta janela aberta. Pressione CTRL+C para parar.")
    ServidorThread(('0.0.0.0', porta), Handler).serve_forever()