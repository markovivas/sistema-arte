# Sistema Arte

Plugin WordPress para gerenciamento de tarefas integrado à API Vikunja.

## Descrição
O **Sistema Arte** é um plugin para WordPress que permite a criação e acompanhamento de demandas de artes gráficas, integrando-se diretamente à API Vikunja para gerenciamento de tarefas. O plugin oferece um formulário customizado para solicitação de demandas e exibe uma lista das tarefas pendentes do projeto configurado.

## Funcionalidades
- Formulário customizado para solicitação de artes, com campos obrigatórios:
  - Título da arte
  - Nome completo do solicitante
  - Secretaria
  - Telefone/WhatsApp
  - Detalhes da solicitação
  - Data de entrega (padrão: hoje + 7 dias às 17h)
  - Prioridade
- Integração direta com a API Vikunja para criação e listagem de tarefas
- Exibição das demandas pendentes em tabela
- Validação de campos obrigatórios e feedback de sucesso/erro
- Interface moderna utilizando Tailwind CSS

## Instalação
1. Faça upload da pasta `sistema-arte` para o diretório `wp-content/plugins/` do seu WordPress.
2. Ative o plugin no painel do WordPress.
3. Configure as variáveis de integração com a API Vikunja em `includes/config.php`:
   - `$apiBase`: URL base da API
   - `$token`: Token de acesso
   - `$projectId`: ID do projeto no Vikunja

## Uso
Adicione o shortcode `[Sistema-Arte]` em qualquer página ou post para exibir o formulário de solicitação e a lista de demandas.

## Estrutura dos Arquivos
- `sistema-arte.php`: Arquivo principal do plugin
- `includes/config.php`: Configurações da API
- `includes/api.php`: Funções de integração com a API Vikunja
- `includes/templates/form.php`: Template do formulário de solicitação
- `includes/templates/tasks.php`: Template da lista de tarefas
- `includes/assets/script.js`: Scripts JS para manipulação do formulário

## Dependências
- [Tailwind CSS](https://tailwindcss.com/) (via CDN)
- jQuery (WordPress padrão)

## Segurança
- Utiliza `wp_nonce_field` para proteção contra CSRF
- Sanitização e validação de todos os campos do formulário
- Escapando de todas as saídas para evitar XSS

## Observações
- O plugin depende de um projeto e token válidos na API Vikunja.
- O usuário do token precisa de permissão de escrita no projeto configurado.
- Em caso de erro de permissão, dados inválidos ou projeto não encontrado, mensagens detalhadas são exibidas ao usuário.

## Autor
Marco Antonio Vivas

---
Plugin desenvolvido para integração com o sistema Vikunja, facilitando a gestão de demandas de artes no WordPress.