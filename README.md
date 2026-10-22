# 📝 Sistema de Leitura Óptica de Cartões de Resposta (OMR)

![Status](https://img.shields.io/badge/status-ativo-brightgreen)
![Versão](https://img.shields.io/badge/versão-1.0.0-blue)
![Contexto](https://img.shields.io/badge/contexto-medicina-red)

Sistema especializado em **visão computacional e leitura óptica (OMR)** para extração automatizada de dados de cartões de resposta preenchidos manualmente, com **correção automática** baseada em gabarito oficial cadastrado manualmente e **geração de relatórios imprimíveis**. Desenvolvido com foco em **precisão absoluta**, sendo ideal para avaliações de alto risco, como provas de medicina, onde um único erro de leitura pode prejudicar o aluno.

## 📋 Índice

- [🎯 Sobre o Projeto](#-sobre-o-projeto)
- [✨ Funcionalidades](#-funcionalidades)
- [🗂 Estrutura do Cartão](#-estrutura-do-cartão)
- [🚀 Como Usar (Fluxo Completo)](#-como-usar-fluxo-completo)
  - [1. Cadastro Manual do Gabarito Oficial](#1-cadastro-manual-do-gabarito-oficial)
  - [2. Extração das Respostas via IA](#2-extração-das-respostas-via-ia)
  - [3. Correção Automática e Geração de Resultados](#3-correção-automática-e-geração-de-resultados)
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

Este sistema foi projetado para realizar a **leitura óptica de cartões de resposta (OMR - Optical Mark Recognition)** a partir de imagens digitalizadas ou fotografadas. O diferencial está no rigor da extração: cada bolinha é analisada pixel por pixel para garantir que a alternativa correta seja identificada sem margem para erros.

O sistema é capaz de:

- Identificar o **nome do aluno** escrito à mão;
- Extrair as **45 respostas** marcadas no gabarito;
- Gerar uma **tabela de conferência** para validação humana;
- **Corrigir automaticamente** a prova com base em um gabarito oficial cadastrado manualmente;
- **Gerar relatórios de notas** (acertos, erros e nota final) por aluno ou para a turma inteira, prontos para impressão.

## ✨ Funcionalidades

| Funcionalidade | Descrição |
|---|---|
| 🔍 **Leitura de Nome** | Extrai o nome manuscrito no campo "Nome:" |
| ⚫ **Detecção de Marcações** | Identifica qual bolinha (A, B, C ou D) está preenchida |
| 📊 **Gabarito Sequencial** | Gera a sequência de 45 respostas pronta para correção |
| 📑 **Tabela de Conferência** | Organiza as respostas por coluna para auditoria |
| 🚫 **Tratamento de Brancos/Nulas** | Detecta questões não respondidas ou com marcação inválida |
| 🔇 **Ignora Ruídos** | Desconsidera a seção "Versão" e textos de instrução |
| 📝 **Cadastro do Gabarito Oficial** | Permite cadastrar manualmente o gabarito da prova antes da correção |
| 🤖 **Integração com IA** | Envia a imagem e o prompt para uma IA de visão computacional extrair as respostas |
| ✅ **Correção Automática** | Compara as respostas do aluno com o gabarito oficial e calcula acertos/erros |
| 🖨 **Relatórios Imprimíveis** | Gera relatórios individuais (por aluno) ou gerais (turma inteira) prontos para impressão |

## 🗂 Estrutura do Cartão

O cartão de respostas segue o padrão:

- **Cabeçalho:**
  - Nome do aluno (manuscrito)
  - Matrícula (manuscrita)
  - Assinatura
  - Versão (A, B, C, D) — *ignorada na extração*
- **Corpo (Gabarito):**
  - **45 questões** divididas em **3 colunas**:
    - Coluna 1: questões **01 a 15**
    - Coluna 2: questões **16 a 30**
    - Coluna 3: questões **31 a 45**
  - Cada questão possui **4 alternativas**: **A, B, C, D**

## 🚀 Como Usar (Fluxo Completo)

O sistema funciona em **três etapas principais**, combinando cadastro manual, extração via IA e correção automática.

### 1. Cadastro Manual do Gabarito Oficial

Antes de corrigir qualquer prova, o professor (ou operador) deve cadastrar o **gabarito oficial** da avaliação diretamente na ferramenta:

- Acesse a seção **"Cadastrar Gabarito"**;
- Informe a **disciplina**, **turma** e **data da prova** (opcional, para organização);
- Preencha manualmente as **45 respostas corretas** (A, B, C ou D) questão por questão;
- Salve o gabarito. Ele ficará armazenado e será usado como referência para todas as correções daquela prova.

> 💡 **Dica:** Você pode cadastrar múltiplos gabaritos (por exemplo, para versões A, B, C e D da mesma prova). Basta selecionar o gabarito correto no momento da correção.

### 2. Extração das Respostas via IA

Após o cadastro do gabarito oficial, é hora de processar os cartões dos alunos:

1. **Envie a imagem do cartão** (digitalizada ou fotografada) para o sistema;
2. O sistema **encaminhará automaticamente a imagem para uma IA de visão computacional** (de sua escolha — ex.: GPT-4o, Claude, Gemini, etc.);
3. O sistema utilizará o **prompt padronizado** localizado na pasta `/prompt` do projeto. Esse prompt instrui a IA a:
   - Ler o **nome do aluno**;
   - Identificar as **45 respostas** marcadas;
   - Retornar os dados em **formato estruturado** (Nome + Gabarito);
4. A IA devolve a resposta no formato padronizado, que é **automaticamente capturada** pela ferramenta.

> 🤖 **Flexibilidade de IA:** O sistema foi projetado para ser **agnóstico em relação à IA utilizada**. Você pode configurar a API da sua preferência (OpenAI, Anthropic, Google, etc.) nas configurações do sistema.

### 3. Correção Automática e Geração de Resultados

Com o gabarito oficial cadastrado e as respostas do aluno extraídas pela IA:

1. A ferramenta **compara automaticamente** as respostas do aluno com o gabarito oficial;
2. Calcula:
   - **Total de acertos**;
   - **Total de erros**;
   - **Questões em branco/nulas** (se houver);
   - **Nota final** (conforme critério de pontuação configurado — ex.: proporcional ou por peso);
3. Gera um **relatório detalhado** com:
   - Nome do aluno;
   - Respostas marcadas vs. gabarito oficial (questão por questão);
   - Acertos, erros e nota final;
4. **Relatórios disponíveis:**
   - 🖨 **Individual:** imprima o resultado de um único aluno (com detalhamento questão a questão);
   - 🖨 **Geral (Turma):** imprima o resultado consolidado de todos os alunos da turma, com ranking e estatísticas (média, maior nota, menor nota, etc.).

> 📄 **Formato de Impressão:** Os relatórios são gerados em formato otimizado para impressão (PDF ou HTML), prontos para serem entregues aos alunos ou arquivados.

## 📏 Regras de Extração

O sistema segue regras **rigorosas** para garantir precisão:

1. ✅ **Não invente respostas** — se uma bolinha estiver vazia, o sistema anota como `Branco` ou `Nula`.
2. ✅ **Não confunda** o número da questão (01, 02...) com as alternativas (A, B, C, D).
3. ✅ **Ignore** a seção "Versão" e o texto de instruções.
4. ✅ **Analise cada linha** individualmente, verificando qual bolinha está totalmente pintada de preto.
5. ✅ **Priorize a precisão sobre a velocidade** — cada marcação é validada pixel por pixel.

## 📤 Formato de Saída

O sistema retorna os dados no seguinte formato padronizado:
