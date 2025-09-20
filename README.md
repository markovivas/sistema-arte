# Sistema Arte

Plugin WordPress para gerenciamento de demandas de arte.

## Descrição
O **Sistema Arte** é um plugin para WordPress que cria um sistema autônomo para gerenciamento de demandas de arte dentro do painel de administração. Ele permite que usuários enviem solicitações através de um formulário e que os administradores gerenciem o fluxo de trabalho usando um quadro Kanban.

## Funcionalidades
- **Formulário de Solicitação:** Um formulário customizado para solicitação de artes, com campos para:
  - Título, nome do solicitante, secretaria, contato.
  - Detalhes da solicitação e anexo de arquivos.
  - Data de entrega e nível de prioridade.
- **Gerenciamento no WordPress:**
  - As demandas são salvas como um tipo de post personalizado ("Demandas de Arte").
  - Utiliza uma taxonomia customizada ("Status") para controlar o fluxo.
- **Quadro Kanban:**
  - Um painel de administração visual com as colunas: `Demanda`, `Fazer`, `Fazendo` e `Feito`.
  - Funcionalidade de arrastar e soltar (drag-and-drop) para mover as demandas entre as colunas e atualizar seu status.
- **IDs Sequenciais:**
  - Sistema de ID personalizado e sequencial (ex: A001, A002) para fácil identificação das demandas.
- **Lista de Demandas Pendentes:**
  - O shortcode exibe uma tabela com todas as demandas que não estão com o status "Feito".
- **Interface Moderna:**
  - Utiliza Tailwind CSS para o formulário e um design limpo para o quadro Kanban.

## Instalação
1. Faça upload da pasta `sistema-arte` para o diretório `wp-content/plugins/` do seu WordPress.
2. Ative o plugin no painel do WordPress.
3. Após a ativação, o menu "Demandas de Arte" aparecerá no painel de administração.

## Uso
Adicione o shortcode `[Sistema-Arte]` em qualquer página ou post para exibir o formulário de solicitação e a lista de demandas.

## Estrutura dos Arquivos
- `sistema-arte.php`: Arquivo principal do plugin
- `includes/templates/form.php`: Template do formulário de solicitação
- `includes/templates/tasks.php`: Template da lista de tarefas
- `includes/assets/script.js`: Scripts JS para o formulário (máscara de telefone)
- `includes/assets/kanban-board.js`: Scripts JS para a funcionalidade do quadro Kanban
- `includes/assets/kanban-style.css`: Estilos CSS para o quadro Kanban

## Dependências
- [Tailwind CSS](https://tailwindcss.com/) (via CDN)
- jQuery (WordPress padrão)
- jQuery UI Sortable (WordPress padrão, para o Kanban)
- jQuery Mask Plugin (via CDN, para o campo de telefone)

## Segurança
- Utiliza `wp_nonce_field` para proteção contra CSRF
- Sanitização e validação de todos os campos do formulário
- Escapando de todas as saídas para evitar XSS

## Autor
Marco Antonio Vivas

---
Plugin desenvolvido para facilitar a gestão de demandas de artes diretamente no WordPress.