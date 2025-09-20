<?php
/**
 * Plugin Name: Sistema Arte
 * Description: Plugin de gerenciamento de tarefas integrado à API Vikunja.  
 * Shortcode disponível: [Sistema-Arte]
 * Version: 1.0
 * Author: Marco Antonio Vivas
 * Text Domain: sistema-arte
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define plugin path
define('SISTEMA_ARTE_PATH', plugin_dir_path(__FILE__));

// Verify and include necessary files
$required_files = [
    //SISTEMA_ARTE_PATH . 'includes/config.php', // Não é mais necessário
    //SISTEMA_ARTE_PATH . 'includes/api.php', // Não é mais necessário
    SISTEMA_ARTE_PATH . 'includes/templates/form.php',
    SISTEMA_ARTE_PATH . 'includes/templates/tasks.php'
];

foreach ($required_files as $file) {
    if (file_exists($file)) {
        require_once $file;
    } else {
        wp_die('Erro fatal: Arquivo necessário não encontrado - ' . esc_html($file));
    }
}

// Enqueue styles and scripts
function sistema_arte_enqueue_assets() {
    // Enqueue Tailwind CSS via CDN
    wp_enqueue_style('sistema-arte-tailwind', 'https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css', [], '2.2.19');

    // Enqueue jQuery Mask Plugin via CDN para o formulário
    wp_enqueue_script('jquery-mask', 'https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js', ['jquery'], '1.14.16', true);

    // Enqueue custom script
    wp_enqueue_script('sistema-arte-script', plugins_url('/includes/assets/script.js', __FILE__), ['jquery'], '1.0.1', true);

    // Localize script for form validation
    wp_localize_script('sistema-arte-script', 'sistemaArte', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('sistema_arte_nonce')
    ]);
}
add_action('wp_enqueue_scripts', 'sistema_arte_enqueue_assets');

function sistema_arte_enqueue_admin_assets($hook) {
    // Enqueue Kanban assets only on our specific admin page
    if ($hook === 'arte_demanda_page_sistema-arte-kanban') {
        wp_enqueue_style('sistema-arte-kanban-style', plugins_url('/includes/assets/kanban-style.css', __FILE__), [], '1.0');
        wp_enqueue_script('sistema-arte-kanban-script', plugins_url('/includes/assets/kanban-board.js', __FILE__), ['jquery', 'jquery-ui-sortable'], '1.1.0', true);
        // Localize script for Kanban AJAX - MOVIDO PARA CÁ
        wp_localize_script('sistema-arte-kanban-script', 'kanban_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('kanban_update_nonce')
        ]);
    }
}
add_action('admin_enqueue_scripts', 'sistema_arte_enqueue_admin_assets');

// Registrar o Custom Post Type para as demandas
function sistema_arte_register_post_type() {
    $labels = [
        'name' => _x('Demandas de Arte', 'Post Type General Name', 'sistema-arte'),
        'singular_name' => _x('Demanda de Arte', 'Post Type Singular Name', 'sistema-arte'),
        'menu_name' => __('Demandas de Arte', 'sistema-arte'),
        'all_items' => __('Todas as Demandas', 'sistema-arte'),
        'add_new_item' => __('Adicionar Nova Demanda', 'sistema-arte'),
        'add_new' => __('Adicionar Nova', 'sistema-arte'),
    ];
    $args = [
        'label' => __('Demanda de Arte', 'sistema-arte'),
        'description' => __('Demandas de arte para o sistema', 'sistema-arte'),
        'labels' => $labels,
        'supports' => ['title', 'editor', 'author', 'custom-fields'],
        'hierarchical' => false,
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 5,
        'menu_icon' => 'dashicons-art',
        'show_in_admin_bar' => true,
        'show_in_nav_menus' => true,
        'can_export' => true,
        'has_archive' => true,
        'exclude_from_search' => false,
        'publicly_queryable' => true,
        'capability_type' => 'post',
    ];
    register_post_type('arte_demanda', $args);
}
add_action('init', 'sistema_arte_register_post_type');

/**
 * Cria a taxonomia 'Status' para as demandas.
 */
function sistema_arte_register_status_taxonomy() {
    $labels = [
        'name' => _x('Status', 'taxonomy general name', 'sistema-arte'),
        'singular_name' => _x('Status', 'taxonomy singular name', 'sistema-arte'),
        'menu_name' => __('Status', 'sistema-arte'),
    ];
    $args = [
        'hierarchical' => false,
        'labels' => $labels,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => ['slug' => 'demanda-status'],
    ];
    register_taxonomy('demanda_status', ['arte_demanda'], $args);

    // Garante que os status padrão existam
    $default_statuses = ['Demanda', 'Fazer', 'Fazendo', 'Feito'];
    foreach ($default_statuses as $status) {
        if (!term_exists($status, 'demanda_status')) {
            wp_insert_term($status, 'demanda_status');
        }
    }
}
add_action('init', 'sistema_arte_register_status_taxonomy');

/**
 * Adiciona a página do Kanban ao menu de administração.
 */
function sistema_arte_add_kanban_page() {
    add_submenu_page(
        'edit.php?post_type=arte_demanda', // Parent slug
        __('Kanban Board', 'sistema-arte'),      // Page title
        __('Kanban', 'sistema-arte'),            // Menu title
        'edit_posts',                            // Capability
        'sistema-arte-kanban',                   // Menu slug
        'sistema_arte_render_kanban_page'        // Callback function
    );
}
add_action('admin_menu', 'sistema_arte_add_kanban_page');

/**
 * Formata o ID da demanda com o prefixo 'A' e preenchimento com zeros.
 */
function sistema_arte_format_id($post_id) {
    // Tenta obter o ID sequencial primeiro. Se não existir, usa o ID do post como fallback.
    $sequential_id = get_post_meta($post_id, '_demanda_id_sequencial', true);
    if (empty($sequential_id)) {
        $sequential_id = $post_id;
    }
    return 'A' . str_pad($sequential_id, 3, '0', STR_PAD_LEFT);
}

// Register shortcode
function sistema_arte_shortcode($atts) {
    // Start output buffering
    ob_start();

    // Não precisamos mais das variáveis de configuração da API
    // global $apiBase, $token, $projectId;

    // Process form submission
    $message = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sistema_arte_nonce']) && wp_verify_nonce($_POST['sistema_arte_nonce'], 'sistema_arte_nonce')) {
        $title = isset($_POST['title']) ? sanitize_text_field(trim($_POST['title'])) : '';
        $full_name = isset($_POST['full_name']) ? sanitize_text_field(trim($_POST['full_name'])) : '';
        $department = isset($_POST['department']) ? sanitize_text_field(trim($_POST['department'])) : '';
        $phone = isset($_POST['phone']) ? sanitize_text_field(trim($_POST['phone'])) : '';
        $additional_info = isset($_POST['additional_info']) ? sanitize_textarea_field(trim($_POST['additional_info'])) : '';
        $due_date = isset($_POST['due_date']) ? sanitize_text_field(trim($_POST['due_date'])) : '';
        $priority = isset($_POST['priority']) ? absint($_POST['priority']) : null;
        
        // Validações
        $errors = [];
        if (empty($title)) {
            $errors[] = 'O título é obrigatório.';
        }
        if (empty($full_name)) {
            $errors[] = 'O nome completo é obrigatório.';
        }
        if (empty($department)) {
            $errors[] = 'A secretaria é obrigatória.';
        }
        if (empty($phone)) {
            $errors[] = 'O telefone/WhatsApp é obrigatório.';
        }
        if (empty($additional_info)) {
            $errors[] = 'Os detalhes da solicitação são obrigatórios.';
        }
        if ($due_date) {
            try {
                $date = new DateTime($due_date);
                $due_date = $date->format('c'); // Formato ISO 8601
            } catch (Exception $e) {
                $errors[] = 'Data de vencimento inválida.';
            }
        }
        if ($priority !== null && ($priority < 1 || $priority > 4)) { // Ajustado para 4 opções
            $errors[] = 'Prioridade inválida.';
        }

        if (empty($errors)) {
            // Format description
            $description = "<div style='font-family: Arial, sans-serif;'>" .
                           "<h3 style='color: #2563eb; border-bottom: 1px solid #ddd; padding-bottom: 5px;'>SOLICITAÇÃO</h3>" .
                           "<ul style='list-style-type: none; padding-left: 0;'>" .
                           "<li><strong>Solicitante:</strong> " . esc_html($full_name) . "</li>" .
                           "<li><strong>Secretaria:</strong> " . esc_html($department) . "</li>" .
                           "<li><strong>Contato:</strong> " . esc_html($phone) . "</li>" .
                           "</ul>" .
                           "<h3 style='color: #2563eb; border-bottom: 1px solid #ddd; padding-bottom: 5px;'>DETALHES</h3>" .
                           "<p style='white-space: pre-line;'>" . esc_html($additional_info) . "</p>" .
                           "</div>";

            $post_data = [
                'post_title' => $title,
                'post_content' => $description,
                'post_type' => 'arte_demanda',
                'post_status' => 'publish', // ou 'pending' se precisar de aprovação
            ];

            // Insere o post no banco de dados
            $post_id = wp_insert_post($post_data);

            if (!is_wp_error($post_id)) {
                // Pega o próximo número do nosso contador sequencial
                $next_id = (int) get_option('sistema_arte_demand_counter', 1);
                update_post_meta($post_id, '_demanda_id_sequencial', $next_id);
                // Incrementa e salva o contador para a próxima demanda
                update_option('sistema_arte_demand_counter', $next_id + 1);

                // Salva os campos customizados como meta dados
                update_post_meta($post_id, '_full_name', $full_name);
                update_post_meta($post_id, '_department', $department);
                update_post_meta($post_id, '_phone', $phone);
                update_post_meta($post_id, '_due_date', $due_date);
                update_post_meta($post_id, '_priority', $priority);

                // Define o status inicial como "Demanda"
                $demanda_term = get_term_by('slug', 'demanda', 'demanda_status');
                if ($demanda_term) {
                    wp_set_object_terms($post_id, $demanda_term->term_id, 'demanda_status');
                }

                // Lida com o upload do anexo
                if (!empty($_FILES['attachment']['name'])) {
                    if (!function_exists('wp_handle_upload')) {
                        require_once(ABSPATH . 'wp-admin/includes/file.php');
                    }
                    if (!function_exists('media_handle_upload')) {
                        require_once(ABSPATH . 'wp-admin/includes/media.php');
                        require_once(ABSPATH . 'wp-admin/includes/image.php');
                    }

                    // 'attachment' é o name do nosso input file
                    $attachment_id = media_handle_upload('attachment', $post_id);

                    if (is_wp_error($attachment_id)) {
                        $errors[] = "Erro ao fazer upload do anexo: " . $attachment_id->get_error_message();
                        // Reverte a criação do post se o anexo for crucial e falhar
                        wp_delete_post($post_id, true);
                        $message = 'Erros no formulário:<ul><li>' . implode('</li><li>', array_map('esc_html', $errors)) . '</li></ul>';
                    }
                }
                
                if (empty($errors)) {
                    $message = "Demanda enviada com sucesso! ID: " . sistema_arte_format_id($post_id);
                    // Limpar o POST para não repopular o formulário
                    $_POST = [];
                }

            } else {
                $message = "Erro ao salvar a demanda: " . $post_id->get_error_message();
            }
        } else {
            $message = 'Erros no formulário:<ul><li>' . implode('</li><li>', array_map('esc_html', $errors)) . '</li></ul>';
        }
    }

    // Listar demandas do CPT 'arte_demanda'
    // Apenas as que NÃO estão com status "Feito"
    $tasks = get_posts([
        'post_type' => 'arte_demanda', 
        'numberposts' => -1, 
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'DESC',
        'tax_query' => [
            [
                'taxonomy' => 'demanda_status',
                'field'    => 'slug',
                'terms'    => 'feito',
                'operator' => 'NOT IN',
            ],
        ],
    ]);

    // Load templates
    ?>
    <div class="container mx-auto px-4 py-8"></br>
        <h1 class="text-3xl font-bold text-gray-800 mb-8 text-center">Sistema de gerenciamento de artes</h1>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <?php
            sistema_arte_form_template($message);
            sistema_arte_tasks_template($tasks);
            ?>
        </div>
    </div>
    <?php

    return ob_get_clean();
}
add_shortcode('Sistema-Arte', 'sistema_arte_shortcode');


/*
 * ===================================================================
 *  MELHORIAS NO PAINEL DE ADMINISTRAÇÃO DO WORDPRESS
 * ===================================================================
 */

/**
 * 1. Adiciona colunas personalizadas à lista de posts de "Demandas de Arte".
 */
function sistema_arte_add_admin_columns($columns) {
    $new_columns = [];
    foreach ($columns as $key => $title) {
        if ($key === 'cb') { $new_columns[$key] = $title; } // Mantém o checkbox
        $new_columns[$key] = $title;
        if ($key === 'title') {
            $new_columns['solicitante'] = __('Solicitante', 'sistema-arte');
            $new_columns['secretaria'] = __('Secretaria', 'sistema-arte');
            $new_columns['due_date'] = __('Data de Entrega', 'sistema-arte');
            $new_columns['priority'] = __('Prioridade', 'sistema-arte');
        }
    }
    // Adiciona a nova coluna de ID no início
    $new_columns = array_merge(['demanda_id' => __('ID Demanda', 'sistema-arte')], $new_columns);
    // Remove colunas que não são tão relevantes para esta tela
    unset($new_columns['author']);
    unset($new_columns['date']);
    return $new_columns;
}
add_filter('manage_arte_demanda_posts_columns', 'sistema_arte_add_admin_columns');

/**
 * 2. Exibe o conteúdo das colunas personalizadas.
 */
function sistema_arte_display_admin_columns($column, $post_id) {
    switch ($column) {
        case 'demanda_id':
            echo '<strong>' . esc_html(sistema_arte_format_id($post_id)) . '</strong>';
            break;

        case 'solicitante':
            echo esc_html(get_post_meta($post_id, '_full_name', true));
            break;

        case 'secretaria':
            echo esc_html(get_post_meta($post_id, '_department', true));
            break;

        case 'due_date':
            $due_date_iso = get_post_meta($post_id, '_due_date', true);
            if ($due_date_iso) {
                try {
                    $date = new DateTime($due_date_iso);
                    echo esc_html($date->format('d/m/Y H:i'));
                } catch (Exception $e) {
                    echo '—';
                }
            } else {
                echo '—';
            }
            break;

        case 'priority':
            $priority = get_post_meta($post_id, '_priority', true);
            $priorities_text = ['1' => 'Alta', '2' => 'Média-Alta', '3' => 'Média', '4' => 'Baixa'];
            echo esc_html($priorities_text[$priority] ?? 'N/D');
            break;
    }
}
add_action('manage_arte_demanda_posts_custom_column', 'sistema_arte_display_admin_columns', 10, 2);

/**
 * 3. Adiciona um Meta Box na tela de edição da demanda para exibir os detalhes.
 */
function sistema_arte_add_details_meta_box() {
    add_meta_box(
        'sistema_arte_details',           // ID do Meta Box
        'Detalhes da Solicitação',        // Título
        'sistema_arte_display_details_meta_box', // Função de callback para renderizar o conteúdo
        'arte_demanda',                   // Post Type
        'normal',                         // Contexto (normal, side)
        'high'                            // Prioridade (high, core, default, low)
    );
}
add_action('add_meta_boxes', 'sistema_arte_add_details_meta_box');

/**
 * Renderiza o conteúdo do Meta Box com os detalhes da demanda.
 */
function sistema_arte_display_details_meta_box($post) {
    // Recupera todos os metadados
    $full_name = get_post_meta($post->ID, '_full_name', true);
    $department = get_post_meta($post->ID, '_department', true);
    $phone = get_post_meta($post->ID, '_phone', true);
    
    // Recupera os anexos associados a este post
    $attachments = get_attached_media('', $post->ID);

    // Exibe os dados de forma organizada
    echo '<h4>Informações do Solicitante</h4>';
    echo '<p><strong>Nome:</strong> ' . esc_html($full_name) . '</p>';
    echo '<p><strong>Secretaria:</strong> ' . esc_html($department) . '</p>';
    echo '<p><strong>Contato:</strong> ' . esc_html($phone) . '</p>';
    
    echo '<hr style="margin: 15px 0;">';
    echo '<h4>Anexos</h4>';
    if ($attachments) {
        echo '<ul>';
        foreach ($attachments as $attachment) {
            echo '<li><a href="' . esc_url(wp_get_attachment_url($attachment->ID)) . '" target="_blank">' . esc_html(get_the_title($attachment->ID)) . '</a></li>';
        }
        echo '</ul>';
    } else {
        echo '<p>Nenhum arquivo foi anexado a esta demanda.</p>';
    }
}

/**
 * Renderiza a página do Kanban Board.
 */
function sistema_arte_render_kanban_page() {
    $statuses = get_terms(['taxonomy' => 'demanda_status', 'hide_empty' => false]);
    $demands_by_status = [];

    foreach ($statuses as $status) {
        $demands_by_status[$status->slug] = get_posts([
            'post_type' => 'arte_demanda',
            'numberposts' => -1,
            'post_status' => 'publish',
            'tax_query' => [
                [
                    'taxonomy' => 'demanda_status',
                    'field' => 'slug',
                    'terms' => $status->slug,
                ],
            ],
        ]);
    }
    $priorities_map = [
        '1' => ['label' => 'Alta', 'class' => 'priority-high'],
        '2' => ['label' => 'Média-Alta', 'class' => 'priority-medium-high'],
        '3' => ['label' => 'Média', 'class' => 'priority-medium'],
        '4' => ['label' => 'Baixa', 'class' => 'priority-low'],
    ];
    ?>
    <div class="wrap">
        <h1>Quadro Kanban de Demandas</h1>
        <div id="kanban-board">
            <?php foreach ($statuses as $status): ?>
                <?php $card_count = count($demands_by_status[$status->slug]); ?>
                <div class="kanban-column">
                    <div class="kanban-column-header">
                        <span><?php echo esc_html($status->name); ?></span>
                        <span class="kanban-card-count"><?php echo esc_html($card_count); ?></span>
                    </div>
                    <div class="kanban-column-body" data-status-slug="<?php echo esc_attr($status->slug); ?>">
                        <?php foreach ($demands_by_status[$status->slug] as $post): ?>
                            <?php
                                $priority_key = get_post_meta($post->ID, '_priority', true) ?: '4';
                                $priority_info = $priorities_map[$priority_key] ?? $priorities_map['4'];
                                $due_date_iso = get_post_meta($post->ID, '_due_date', true);
                                $due_date_formatted = $due_date_iso ? (new DateTime($due_date_iso))->format('d/m/Y') : 'Sem prazo';
                            ?>
                            <div class="kanban-card <?php echo esc_attr($priority_info['class']); ?>" data-post-id="<?php echo esc_attr($post->ID); ?>">
                                <div class="kanban-card-title">
                                    <a href="<?php echo get_edit_post_link($post->ID); ?>" target="_blank">
                                        <?php echo esc_html(sistema_arte_format_id($post->ID)); ?> - <?php echo esc_html($post->post_title); ?>
                                    </a>
                                </div>
                                <div class="kanban-card-meta">
                                    <span class="meta-item">
                                        <span class="dashicons dashicons-admin-users"></span>
                                        <?php echo esc_html(get_post_meta($post->ID, '_full_name', true)); ?>
                                    </span>
                                    <span class="meta-item">
                                        <span class="dashicons dashicons-calendar-alt"></span>
                                        <?php echo esc_html($due_date_formatted); ?>
                                    </span>
                                </div>
                                <div class="kanban-card-footer">
                                    <span class="kanban-priority-badge"><?php echo esc_html($priority_info['label']); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
}

/**
 * Função auxiliar para gerar o HTML do rodapé do card.
 */
function sistema_arte_get_card_footer_html($post_id) {
    $priorities_map = [
        '1' => ['label' => 'Alta', 'class' => 'priority-high'],
        '2' => ['label' => 'Média-Alta', 'class' => 'priority-medium-high'],
        '3' => ['label' => 'Média', 'class' => 'priority-medium'],
        '4' => ['label' => 'Baixa', 'class' => 'priority-low'],
    ];
    $priority_key = get_post_meta($post_id, '_priority', true) ?: '4';
    $priority_info = $priorities_map[$priority_key] ?? $priorities_map['4'];
    return '<span class="kanban-priority-badge">' . esc_html($priority_info['label']) . '</span>';
}

/**
 * Manipulador AJAX para atualizar o status da demanda.
 */
function sistema_arte_update_demand_status() {
    // 1. Segurança: Verificar nonce e permissões
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'kanban_update_nonce')) {
        wp_send_json_error(['message' => 'Falha na verificação de segurança.'], 403);
    }
    if (!current_user_can('edit_posts')) {
        wp_send_json_error(['message' => 'Você não tem permissão para fazer isso.'], 403);
    }

    // 2. Sanitizar dados de entrada
    $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
    $new_status_slug = isset($_POST['new_status']) ? sanitize_key($_POST['new_status']) : '';

    if (!$post_id || empty($new_status_slug)) {
        wp_send_json_error(['message' => 'Dados inválidos.'], 400);
    }

    // 3. Lógica de atualização
    $term = get_term_by('slug', $new_status_slug, 'demanda_status');
    if (!$term) {
        wp_send_json_error(['message' => 'Status não encontrado.'], 404);
    }

    // Atualiza o termo (status) do post
    $result = wp_set_object_terms($post_id, $term->term_id, 'demanda_status');

    if (is_wp_error($result)) {
        wp_send_json_error(['message' => $result->get_error_message()], 500);
    }

    // 4. Enviar resposta de sucesso
    wp_send_json_success([
        'message' => 'Status atualizado com sucesso!',
        'footer_html' => sistema_arte_get_card_footer_html($post_id)
    ]);
}
add_action('wp_ajax_update_demand_status', 'sistema_arte_update_demand_status');

?>