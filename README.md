# 📝 Sistema de Leitura Óptica de Cartões de Resposta (OMR)

![Status](https://img.shields.io/badge/status-ativo-brightgreen)
![Versão](https://img.shields.io/badge/vers%C3%A3o-1.0.0-blue)
![Contexto](https://img.shields.io/badge/contexto-medicina-red)

Sistema especializado em visão computacional e leitura óptica (OMR) para extração automatizada de dados de cartões de resposta preenchidos manualmente, com correção automática baseada em gabarito oficial cadastrado manualmente e geração de relatórios imprimíveis. Desenvolvido com foco em precisão absoluta, sendo ideal para avaliações de alto risco, como provas de medicina, onde um único erro de leitura pode prejudicar o aluno.

---

## 📋 Índice

- [🎯 Sobre o Projeto](#-sobre-o-projeto)
- [✨ Funcionalidades](#-funcionalidades)
- [🗂 Estrutura do Cartão](#-estrutura-do-cartão)
- [🚀 Como Usar (Fluxo Completo)](#-como-usar-fluxo-completo)
  - [1. Cadastro Manual do Gabarito Oficial](#1-cadastro-manual-do-gabarito-oficial)
  - [2. Extração das Respostas via IA](#2-extração-das-respostas-via-ia)
  - [3. Correção Automática e Geração de Resultados](#3-correção-automática-e-geração-de-resultados)
- [🧠 Arquitetura de Visão Computacional](#-arquitetura-de-visão-computacional)
  - [Por que OpenCV?](#por-que-opencv)
  - [O que faz o `ler_cartao.py`](#o-que-faz-o-ler_cartaopy)
  - [O que faz o `servidor.py`](#o-que-faz-o-servidorpy)
  - [Como os dois se comunicam com o frontend](#como-os-dois-se-comunicam-com-o-frontend)
- [📏 Regras de Extração](#-regras-de-extração)
- [📤 Formato de Saída](#-formato-de-saída)
- [🧪 Exemplo de Uso](#-exemplo-de-uso)
- [✅ Boas Práticas](#-boas-práticas)
- [⚠️ Limitações e Cuidados](#️-limitações-e-cuidados)
- [🛠 Tecnologias Envolvidas](#-tecnologias-envolvidas)
- [🤝 Contribuição](#-contribuição)
- [📄 Licença](#-licença)

---

## 🎯 Sobre o Projeto

Este sistema foi projetado para realizar a leitura óptica de cartões de resposta (OMR - Optical Mark Recognition) a partir de imagens digitalizadas ou fotografadas. O diferencial está no rigor da extração: cada bolinha é analisada pixel por pixel para garantir que a alternativa correta seja identificada sem margem para erros.

O sistema é capaz de:

- Identificar o nome do aluno escrito à mão;
- Extrair as 45 respostas marcadas no gabarito;
- Gerar uma tabela de conferência para validação humana;
- Corrigir automaticamente a prova com base em um gabarito oficial cadastrado manualmente;
- Gerar relatórios de notas (acertos, erros e nota final) por aluno ou para a turma inteira, prontos para impressão.

---

## ✨ Funcionalidades

| Funcionalidade | Descrição |
|---|---|
| 🔍 Leitura de Nome | Extrai o nome manuscrito no campo "Nome:" |
| ⚫ Detecção de Marcações | Identifica qual bolinha (A, B, C ou D) está preenchida |
| 📊 Gabarito Sequencial | Gera a sequência de 45 respostas pronta para correção |
| 📑 Tabela de Conferência | Organiza as respostas por coluna para auditoria |
| 🚫 Tratamento de Brancos/Nulas | Detecta questões não respondidas ou com marcação inválida |
| 🔇 Ignora Ruídos | Desconsidera a seção "Versão" e textos de instrução |
| 📝 Cadastro do Gabarito Oficial | Permite cadastrar manualmente o gabarito da prova antes da correção |
| 🤖 Integração com IA | Envia a imagem e o prompt para uma IA de visão computacional extrair as respostas |
| ✅ Correção Automática | Compara as respostas do aluno com o gabarito oficial e calcula acertos/erros |
| 🖨 Relatórios Imprimíveis | Gera relatórios individuais (por aluno) ou gerais (turma inteira) prontos para impressão |

---

## 🗂 Estrutura do Cartão

O cartão de respostas segue o padrão:

### Cabeçalho

- Nome do aluno (manuscrito)
- Matrícula (manuscrita)
- Assinatura
- Versão (A, B, C, D) — ignorada na extração

### Corpo (Gabarito)

45 questões divididas em 3 colunas:

- **Coluna 1:** questões 01 a 15
- **Coluna 2:** questões 16 a 30
- **Coluna 3:** questões 31 a 45

Cada questão possui 4 alternativas: A, B, C, D.

---

## 🚀 Como Usar (Fluxo Completo)

O sistema funciona em três etapas principais, combinando cadastro manual, extração via IA e correção automática.

### 1. Cadastro Manual do Gabarito Oficial

Antes de corrigir qualquer prova, o professor (ou operador) deve cadastrar o gabarito oficial da avaliação diretamente na ferramenta:

1. Acesse a seção **"Cadastrar Gabarito"**;
2. Informe a disciplina, turma e data da prova (opcional, para organização);
3. Preencha manualmente as 45 respostas corretas (A, B, C ou D) questão por questão;
4. Salve o gabarito. Ele ficará armazenado e será usado como referência para todas as correções daquela prova.

💡 **Dica:** Você pode cadastrar múltiplos gabaritos (por exemplo, para versões A, B, C e D da mesma prova). Basta selecionar o gabarito correto no momento da correção.

### 2. Extração das Respostas via IA

Após o cadastro do gabarito oficial, é hora de processar os cartões dos alunos:

1. Envie a imagem do cartão (digitalizada ou fotografada) para o sistema;
2. O sistema encaminhará automaticamente a imagem para uma IA de visão computacional (de sua escolha — ex.: GPT-4o, Claude, Gemini, etc.);
3. O sistema utilizará o prompt padronizado localizado na pasta `/prompt` do projeto. Esse prompt instrui a IA a:
   - Ler o nome do aluno;
   - Identificar as 45 respostas marcadas;
   - Retornar os dados em formato estruturado (Nome + Gabarito);
4. A IA devolve a resposta no formato padronizado, que é automaticamente capturada pela ferramenta.

🤖 **Flexibilidade de IA:** O sistema foi projetado para ser agnóstico em relação à IA utilizada. Você pode configurar a API da sua preferência (OpenAI, Anthropic, Google, etc.) nas configurações do sistema.

### 3. Correção Automática e Geração de Resultados

Com o gabarito oficial cadastrado e as respostas do aluno extraídas pela IA:

1. A ferramenta compara automaticamente as respostas do aluno com o gabarito oficial;
2. Calcula:
   - Total de acertos;
   - Total de erros;
   - Questões em branco/nulas (se houver);
   - Nota final (conforme critério de pontuação configurado — ex.: proporcional ou por peso);
3. Gera um relatório detalhado com:
   - Nome do aluno;
   - Respostas marcadas vs. gabarito oficial (questão por questão);
   - Acertos, erros e nota final.

#### Relatórios disponíveis

- 🖨 **Individual:** imprima o resultado de um único aluno (com detalhamento questão a questão);
- 🖨 **Geral (Turma):** imprima o resultado consolidado de todos os alunos da turma, com ranking e estatísticas (média, maior nota, menor nota, etc.).

📄 **Formato de Impressão:** Os relatórios são gerados em formato otimizado para impressão (PDF ou HTML), prontos para serem entregues aos alunos ou arquivados.

---

## 🧠 Arquitetura de Visão Computacional

A leitura óptica do cartão não depende de IA externa. Ela é feita **localmente**, com Python + OpenCV, através de dois arquivos principais que vivem na pasta `visao_computacional/`:

```
visao_computacional/
├── ler_cartao.py       ← motor de OMR (detecta as bolinhas e as marcações)
├── servidor.py         ← expõe o motor via HTTP para o navegador
└── requirements.txt    ← dependências (opencv-python, numpy)
```

### Por que OpenCV?

O **OpenCV** (Open Source Computer Vision Library) é a biblioteca responsável por toda a parte de processamento de imagem do projeto. Sem ela, nada funciona. É ela que executa:

- **Leitura da imagem** (`cv2.imread` / `cv2.imdecode`);
- **Conversão para escala de cinza** (`cv2.cvtColor`);
- **Suavização** (`cv2.GaussianBlur`) — reduz ruído antes da binarização;
- **Binarização de Otsu** (`cv2.threshold`) — separa o que é "tinta" (preto) do que é "papel" (branco), automaticamente;
- **Detecção de contornos** (`cv2.findContours`) — encontra todas as formas fechadas na imagem (bolinhas, quadrados pretos, letras, etc.);
- **Cálculo de área, perímetro e circularidade** (`cv2.contourArea`, `cv2.arcLength`) — permite descartar tudo que não é bolinha;
- **Detecção do círculo envolvente** (`cv2.minEnclosingCircle`) — devolve centro (x, y) e raio de cada bolinha.

Sem OpenCV, teríamos que reimplementar tudo isso manualmente — lento, frágil e impreciso. Por isso, a instalação dela é **obrigatória**:

```bash
py -m pip install opencv-python numpy
```

### O que faz o `ler_cartao.py`

É o **motor de leitura** propriamente dito. Não sobe servidor, não conversa com o navegador. Recebe uma imagem e devolve as 45 respostas.

Pipeline interno:

1. **Pré-processamento** — converte para cinza, borra, binariza com Otsu.
2. **Detecção de bolinhas** (`detectar_bolinhas`):
   - Encontra contornos;
   - Filtra por área (80–2500 px), perímetro, **circularidade ≥ 0.78** e **aspecto 0.75–1.25**. Isso elimina quadrados pretos dos cantos, letras, números de questão e ruídos;
   - Guarda centro (x, y) e raio de cada bolinha válida.
3. **Agrupamento em linhas** (`agrupar_linhas`):
   - Ordena as bolinhas por Y;
   - Agrupa as que têm Y próximos (tolerância proporcional ao raio mediano) → cada grupo = uma linha do cartão.
4. **Seleção das 15 linhas de questões**:
   - Descarta linhas com menos de 8 bolinhas (cabeçalho, versão, instruções);
   - Pega as 15 linhas mais "cheias";
   - Reordena por Y → questão 1 na primeira linha, questão 15 na última.
5. **Separação em 3 colunas × 4 alternativas**:
   - Dentro de cada linha, ordena por X;
   - Descarta outliers de raio;
   - Divide em 3 grupos de 4 (coluna 1 = questões 1–15, coluna 2 = 16–30, coluna 3 = 31–45).
6. **Detecção da marcação** (pixel a pixel):
   - Para cada bolinha, extrai uma ROI central;
   - Calcula a média de luminância (0 = preto, 255 = branco);
   - A bolinha marcada é a **mais escura**;
   - Só aceita como marcada se a diferença para as outras 3 for grande o bastante (`dens_min < 110` e `média_outras − dens_min > 40`). Caso contrário, marca em branco (evita falso positivo).
7. **Retorno** — dicionário `{1: "C", 2: "B", ..., 45: "A"}` com as 45 respostas.

Também é utilizável pela linha de comando (modo antigo de correção aluno × gabarito):

```bash
py ler_cartao.py --gabarito gabarito.png --aluno cartao.png --saida resultado.json
```

### O que faz o `servidor.py`

É a **ponte** entre o navegador e o motor de leitura. O JavaScript do navegador **não executa Python**, então precisa de um servidor HTTP local para chamar o `ler_cartao.py`.

O que ele faz:

1. Sobe um servidor HTTP em `http://localhost:5000`.
2. Aceita **POST** com a imagem do cartão no corpo da requisição (`multipart/form-data`).
3. Extrai os bytes da imagem do corpo (procura assinatura JPEG `FF D8 FF` ou PNG `89 50 4E 47`).
4. Decodifica em memória com `cv2.imdecode` (não salva em disco).
5. Chama `ler_cartao(...)` — a mesma função do motor.
6. Responde em **JSON**:
   ```json
   { "ok": true, "respostas": { "1": "C", "2": "B", ... } }
   ```
   Ou, em caso de erro:
   ```json
   { "ok": false, "erro": "Detectei apenas X bolinhas..." }
   ```
7. Habilita **CORS** (`Access-Control-Allow-Origin: *`) para o `index.html` conseguir chamar `localhost:5000` estando em `localhost/CORRETOR-PROVAS/...`.

### Como os dois se comunicam com o frontend

```
┌────────────────────────┐        POST (imagem)         ┌─────────────────────┐
│ index.html             │ ───────────────────────────► │ servidor.py         │
│ (navegador do usuário) │                              │ localhost:5000      │
│                        │ ◄─────────────────────────── │                     │
└────────────────────────┘     JSON { ok, respostas }   └──────────┬──────────┘
                                                                   │ chama
                                                                   ▼
                                                        ┌─────────────────────┐
                                                        │ ler_cartao.py       │
                                                        │ (OpenCV, OMR)       │
                                                        └─────────────────────┘
```

Fluxo prático:

1. O usuário abre `frontend/index.html` no navegador.
2. Faz upload da imagem do gabarito e clica em **Processar Gabarito**.
3. O JS monta um `FormData` e faz `fetch('http://localhost:5000/ler', { method: 'POST', body: form })`.
4. O `servidor.py` recebe, chama `ler_cartao.py` e devolve as respostas em JSON.
5. O JS preenche a tabela `Respostas Lidas` na tela.

**Importante:** o `servidor.py` **precisa estar rodando** enquanto o usuário usa a leitura por imagem. Se ele for fechado, o botão "Processar Gabarito" retorna erro de rede.

---

## 📏 Regras de Extração

O sistema segue regras rigorosas para garantir precisão:

- ✅ Não invente respostas — se uma bolinha estiver vazia, o sistema anota como Branco ou Nula.
- ✅ Não confunda o número da questão (01, 02...) com as alternativas (A, B, C, D).
- ✅ Ignore a seção "Versão" e o texto de instruções.
- ✅ Analise cada linha individualmente, verificando qual bolinha está totalmente pintada de preto.
- ✅ Priorize a precisão sobre a velocidade — cada marcação é validada pixel por pixel.

---

## 📤 Formato de Saída

O sistema retorna os dados no seguinte formato padronizado:

```text
**Nome:** [Nome extraído]
**Matrícula:** [Número extraído]

**Gabarito do Aluno (Cole na sua ferramenta):**
[45 respostas em sequência, separadas por espaço]

**Tabela para Conferência:**
Coluna 1 (01-15): 01-[Letra], 02-[Letra], 03-[Letra]...
Coluna 2 (16-30): 16-[Letra], 17-[Letra], 18-[Letra]...
Coluna 3 (31-45): 31-[Letra], 32-[Letra], 33-[Letra]...
```

### Exemplo Real

```text
**Nome:** Eduardo André Cardoso Waterloo Filho
**Matrícula:** 231034499

**Gabarito do Aluno (Cole na sua ferramenta):**
C B C A B B D A C B D C B B D C C A D B B B A D A A D C C D C A C B B D A B D B A B A A C

**Tabela para Conferência:**
Coluna 1 (01-15): 01-C, 02-B, 03-C, 04-A, 05-B, 06-B, 07-D, 08-A, 09-C, 10-B, 11-D, 12-C, 13-B, 14-B, 15-D
Coluna 2 (16-30): 16-C, 17-C, 18-A, 19-D, 20-B, 21-B, 22-B, 23-A, 24-D, 25-A, 26-A, 27-D, 28-C, 29-C, 30-D
Coluna 3 (31-45): 31-C, 32-A, 33-C, 34-B, 35-B, 36-D, 37-A, 38-B, 39-D, 40-B, 41-A, 42-B, 43-A, 44-A, 45-C
```

---

## ✅ Boas Práticas

- 📸 Sempre utilize imagens de boa qualidade — a precisão depende diretamente da nitidez;
- 🖨 Imprima o cartão em tamanho real (100%) para evitar distorções;
- 🖊 Use caneta azul ou preta para preenchimento, evitando lápis;
- ⭕ Pinte completamente o círculo da alternativa escolhida, sem usar X, ✓ ou traços;
- 🚫 Evite rasuras e não dobre o cartão;
- 🔍 Faça um teste prévio no scanner antes da aplicação oficial;
- 👁 Sempre valide o resultado com a tabela de conferência;
- 📝 Cadastre o gabarito oficial com atenção — ele é a base de toda a correção;
- 🐍 Mantenha `opencv-python` e `numpy` instalados no mesmo Python que roda o `servidor.py`;
- 🟢 Deixe o `servidor.py` rodando durante toda a sessão de leitura pelo navegador;

---

## ⚠️ Limitações e Cuidados

- O sistema não substitui a conferência humana em casos de marcações ambíguas;
- Marcações parciais (bolinhas meio pintadas) podem gerar resultados inconclusivos;
- Imagens com baixa resolução, sombras ou reflexos podem comprometer a leitura;
- Cartões com rasuras, dobras ou impressões fora do padrão devem ser tratados manualmente;
- Em caso de dúvida, o sistema sinaliza a questão como "Nula" para revisão;
- O cadastro manual do gabarito deve ser feito com atenção, pois erros nele comprometem toda a correção;
- O `servidor.py` usa a porta `5000` — se ela estiver ocupada, altere no código e no `index.html`;
- O OpenCV precisa estar instalado no Python correto (use `py -m pip install ...` se `pip` não for reconhecido);

⚠️ **Aviso:** Em avaliações de alto risco (como provas de medicina), a dupla checagem é fortemente recomendada. A precisão absoluta é o objetivo, mas a validação humana é a garantia final.

---

## 🛠 Tecnologias Envolvidas

- **OpenCV** — biblioteca central de visão computacional (detecção de contornos, binarização, análise de ROI);
- **NumPy** — manipulação vetorial das imagens e cálculos estatísticos (mediana de raios, média de luminância);
- **Python 3** — linguagem do motor de leitura e do servidor HTTP;
- **http.server** — servidor HTTP nativo do Python, sem dependências extras;
- **OMR (Optical Mark Recognition)** — reconhecimento óptico de marcações;
- **Processamento Pixel a Pixel** — detecção precisa das bolinhas preenchidas;
- **HTML/CSS/JavaScript** — frontend de upload e visualização;
- **Geração de Relatórios** — PDF/HTML para impressão individual ou em lote.

---

## 🤝 Contribuição

Contribuições são bem-vindas! Para contribuir:

1. Faça um fork do projeto;
2. Crie uma branch para sua feature (`git checkout -b feature/nova-funcionalidade`);
3. Commit suas alterações (`git commit -m 'Adiciona nova funcionalidade'`);
4. Push para a branch (`git push origin feature/nova-funcionalidade`);
5. Abra um Pull Request.

---

## 📄 Licença

Este projeto está licenciado sob a MIT License. Consulte o arquivo `LICENSE` para mais detalhes.
