📝 Sistema de Leitura Óptica de Cartões de Resposta (OMR)
https://img.shields.io/badge/status-ativo-brightgreen
https://img.shields.io/badge/vers%C3%A3o-1.0.0-blue
https://img.shields.io/badge/contexto-medicina-red

Sistema especializado em visão computacional e leitura óptica (OMR) para extração automatizada de dados de cartões de resposta preenchidos manualmente, com correção automática baseada em gabarito oficial cadastrado manualmente e geração de relatórios imprimíveis. Desenvolvido com foco em precisão absoluta, sendo ideal para avaliações de alto risco, como provas de medicina, onde um único erro de leitura pode prejudicar o aluno.

📋 Índice
Sobre o Projeto

Funcionalidades

Estrutura do Cartão

Como Usar (Fluxo Completo)

1. Cadastro Manual do Gabarito Oficial

2. Extração das Respostas via IA

3. Correção Automática e Geração de Resultados

Regras de Extração

Formato de Saída

Exemplo de Uso

Boas Práticas

Limitações e Cuidados

Tecnologias Envolvidas

Contribuição

Licença

🎯 Sobre o Projeto
Este sistema foi projetado para realizar a leitura óptica de cartões de resposta (OMR - Optical Mark Recognition) a partir de imagens digitalizadas ou fotografadas. O diferencial está no rigor da extração: cada bolinha é analisada pixel por pixel para garantir que a alternativa correta seja identificada sem margem para erros.

O sistema é capaz de:

Identificar o nome do aluno escrito à mão;

Extrair as 45 respostas marcadas no gabarito;

Gerar uma tabela de conferência para validação humana;

Corrigir automaticamente a prova com base em um gabarito oficial cadastrado manualmente;

Gerar relatórios de notas (acertos, erros e nota final) por aluno ou para a turma inteira, prontos para impressão.

✨ Funcionalidades
Funcionalidade	Descrição
🔍 Leitura de Nome	Extrai o nome manuscrito no campo "Nome:"
⚫ Detecção de Marcações	Identifica qual bolinha (A, B, C ou D) está preenchida
📊 Gabarito Sequencial	Gera a sequência de 45 respostas pronta para correção
📑 Tabela de Conferência	Organiza as respostas por coluna para auditoria
🚫 Tratamento de Brancos/Nulas	Detecta questões não respondidas ou com marcação inválida
🔇 Ignora Ruídos	Desconsidera a seção "Versão" e textos de instrução
📝 Cadastro do Gabarito Oficial	Permite cadastrar manualmente o gabarito da prova antes da correção
🤖 Integração com IA	Envia a imagem e o prompt para uma IA de visão computacional extrair as respostas
✅ Correção Automática	Compara as respostas do aluno com o gabarito oficial e calcula acertos/erros
🖨 Relatórios Imprimíveis	Gera relatórios individuais (por aluno) ou gerais (turma inteira) prontos para impressão
🗂 Estrutura do Cartão
O cartão de respostas segue o padrão:

Cabeçalho:

Nome do aluno (manuscrito)

Matrícula (manuscrita)

Assinatura

Versão (A, B, C, D) — ignorada na extração

Corpo (Gabarito):

45 questões divididas em 3 colunas:

Coluna 1: questões 01 a 15

Coluna 2: questões 16 a 30

Coluna 3: questões 31 a 45

Cada questão possui 4 alternativas: A, B, C, D

🚀 Como Usar (Fluxo Completo)
O sistema funciona em três etapas principais, combinando cadastro manual, extração via IA e correção automática.

1. Cadastro Manual do Gabarito Oficial
Antes de corrigir qualquer prova, o professor (ou operador) deve cadastrar o gabarito oficial da avaliação diretamente na ferramenta:

Acesse a seção "Cadastrar Gabarito";

Informe a disciplina, turma e data da prova (opcional, para organização);

Preencha manualmente as 45 respostas corretas (A, B, C ou D) questão por questão;

Salve o gabarito. Ele ficará armazenado e será usado como referência para todas as correções daquela prova.

💡 Dica: Você pode cadastrar múltiplos gabaritos (por exemplo, para versões A, B, C e D da mesma prova). Basta selecionar o gabarito correto no momento da correção.

2. Extração das Respostas via IA
Após o cadastro do gabarito oficial, é hora de processar os cartões dos alunos:

Envie a imagem do cartão (digitalizada ou fotografada) para o sistema;

O sistema encaminhará automaticamente a imagem para uma IA de visão computacional (de sua escolha — ex.: GPT-4o, Claude, Gemini, etc.);

O sistema utilizará o prompt padronizado localizado na pasta /prompt do projeto. Esse prompt instrui a IA a:

Ler o nome do aluno;

Identificar as 45 respostas marcadas;

Retornar os dados em formato estruturado (Nome + Gabarito);

A IA devolve a resposta no formato padronizado, que é automaticamente capturada pela ferramenta.

🤖 Flexibilidade de IA: O sistema foi projetado para ser agnóstico em relação à IA utilizada. Você pode configurar a API da sua preferência (OpenAI, Anthropic, Google, etc.) nas configurações do sistema.

3. Correção Automática e Geração de Resultados
Com o gabarito oficial cadastrado e as respostas do aluno extraídas pela IA:

A ferramenta compara automaticamente as respostas do aluno com o gabarito oficial;

Calcula:

Total de acertos;

Total de erros;

Questões em branco/nulas (se houver);

Nota final (conforme critério de pontuação configurado — ex.: proporcional ou por peso);

Gera um relatório detalhado com:

Nome do aluno;

Respostas marcadas vs. gabarito oficial (questão por questão);

Acertos, erros e nota final;

Relatórios disponíveis:

🖨 Individual: imprima o resultado de um único aluno (com detalhamento questão a questão);

🖨 Geral (Turma): imprima o resultado consolidado de todos os alunos da turma, com ranking e estatísticas (média, maior nota, menor nota, etc.).

📄 Formato de Impressão: Os relatórios são gerados em formato otimizado para impressão (PDF ou HTML), prontos para serem entregues aos alunos ou arquivados.

📏 Regras de Extração
O sistema segue regras rigorosas para garantir precisão:

✅ Não invente respostas — se uma bolinha estiver vazia, o sistema anota como Branco ou Nula.

✅ Não confunda o número da questão (01, 02...) com as alternativas (A, B, C, D).

✅ Ignore a seção "Versão" e o texto de instruções.

✅ Analise cada linha individualmente, verificando qual bolinha está totalmente pintada de preto.

✅ Priorize a precisão sobre a velocidade — cada marcação é validada pixel por pixel.

📤 Formato de Saída
O sistema retorna os dados no seguinte formato padronizado:

text
**Nome:** [Nome extraído]
**Matrícula:** [Número extraído]

**Gabarito do Aluno (Cole na sua ferramenta):**
[45 respostas em sequência, separadas por espaço]

**Tabela para Conferência:**
Coluna 1 (01-15): 01-[Letra], 02-[Letra], 03-[Letra]...
Coluna 2 (16-30): 16-[Letra], 17-[Letra], 18-[Letra]...
Coluna 3 (31-45): 31-[Letra], 32-[Letra], 33-[Letra]...
Exemplo Real
text
**Nome:** Eduardo André Cardoso Waterloo Filho
**Matrícula:** 231034499

**Gabarito do Aluno (Cole na sua ferramenta):**
C B C A B B D A C B D C B B D C C A D B B B A D A A D C C D C A C B B D A B D B A B A A C

**Tabela para Conferência:**
Coluna 1 (01-15): 01-C, 02-B, 03-C, 04-A, 05-B, 06-B, 07-D, 08-A, 09-C, 10-B, 11-D, 12-C, 13-B, 14-B, 15-D
Coluna 2 (16-30): 16-C, 17-C, 18-A, 19-D, 20-B, 21-B, 22-B, 23-A, 24-D, 25-A, 26-A, 27-D, 28-C, 29-C, 30-D
Coluna 3 (31-45): 31-C, 32-A, 33-C, 34-B, 35-B, 36-D, 37-A, 38-B, 39-D, 40-B, 41-A, 42-B, 43-A, 44-A, 45-C
✅ Boas Práticas
📸 Sempre utilize imagens de boa qualidade — a precisão depende diretamente da nitidez;

🖨 Imprima o cartão em tamanho real (100%) para evitar distorções;

🖊 Use caneta azul ou preta para preenchimento, evitando lápis;

⭕ Pinte completamente o círculo da alternativa escolhida, sem usar X, ✓ ou traços;

🚫 Evite rasuras e não dobre o cartão;

🔍 Faça um teste prévio no scanner antes da aplicação oficial;

👁 Sempre valide o resultado com a tabela de conferência;

📝 Cadastre o gabarito oficial com atenção — ele é a base de toda a correção;

🤖 Utilize uma IA de visão confiável e mantenha o prompt da pasta /prompt atualizado.

⚠️ Limitações e Cuidados
O sistema não substitui a conferência humana em casos de marcações ambíguas;

Marcações parciais (bolinhas meio pintadas) podem gerar resultados inconclusivos;

Imagens com baixa resolução, sombras ou reflexos podem comprometer a leitura;

Cartões com rasuras, dobras ou impressões fora do padrão devem ser tratados manualmente;

Em caso de dúvida, o sistema sinaliza a questão como "Nula" para revisão;

A qualidade do prompt enviado à IA impacta diretamente a precisão da extração — revise-o periodicamente;

O cadastro manual do gabarito deve ser feito com atenção, pois erros nele comprometem toda a correção.

⚠️ Aviso: Em avaliações de alto risco (como provas de medicina), a dupla checagem é fortemente recomendada. A precisão absoluta é o objetivo, mas a validação humana é a garantia final.

🛠 Tecnologias Envolvidas
Visão Computacional — processamento e análise de imagens;

OMR (Optical Mark Recognition) — reconhecimento óptico de marcações;

Processamento de Imagem Pixel a Pixel — para detecção precisa das bolinhas preenchidas;

OCR (Optical Character Recognition) — para leitura de textos manuscritos (nome e matrícula);

IA de Visão Computacional — integração com modelos como GPT-4o, Claude, Gemini (via API);

Geração de Relatórios — PDF/HTML para impressão individual ou em lote.

🤝 Contribuição
Contribuições são bem-vindas! Para contribuir:

Faça um fork do projeto;

Crie uma branch para sua feature (git checkout -b feature/nova-funcionalidade);

Commit suas alterações (git commit -m 'Adiciona nova funcionalidade');

Push para a branch (git push origin feature/nova-funcionalidade);

Abra um Pull Request.

📄 Licença
Este projeto está licenciado sob a MIT License. Consulte o arquivo LICENSE para mais detalhes.
