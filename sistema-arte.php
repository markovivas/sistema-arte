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
    SISTEMA_ARTE_PATH . 'includes/config.php',
    SISTEMA_ARTE_PATH . 'includes/api.php',
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
    
    // Enqueue custom script
    wp_enqueue_script('sistema-arte-script', plugins_url('/includes/assets/script.js', __FILE__), ['jquery'], '1.0.1', true);
    
    // Localize script for form validation
    wp_localize_script('sistema-arte-script', 'sistemaArte', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('sistema_arte_nonce')
    ]);
}
add_action('wp_enqueue_scripts', 'sistema_arte_enqueue_assets');

// Register shortcode
function sistema_arte_shortcode($atts) {
    // Start output buffering
    ob_start();

    // Load config variables
    global $apiBase, $token, $projectId;

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
        if ($priority !== null && ($priority < 1 || $priority > 5)) {
            $errors[] = 'Prioridade deve ser entre 1 e 5.';
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

            $task = [
                'title' => $title,
                'project_id' => $projectId,
                'description' => $description,
            ];
            if ($due_date) {
                $task['due_date'] = $due_date;
            }
            if ($priority !== null) {
                $task['priority'] = $priority;
            }

            $result = addTask($apiBase, $token, $task, $projectId);
            if ($result['success']) {
                $message = "Tarefa criada com sucesso! ID: {$result['data']['id']}";
            } else {
                $message = "Erro ao criar tarefa: {$result['error']} (HTTP {$result['http_code']})";
                if ($result['http_code'] == 403) {
                    $message .= "<br>Permissão negada. Verifique se o usuário do token tem acesso de escrita no projeto.";
                } elseif ($result['http_code'] == 400) {
                    $message .= "<br>Dados inválidos. Verifique o formato dos campos da tarefa.";
                } elseif ($result['http_code'] == 404) {
                    $message .= "<br>Projeto não encontrado. Confirme se o project_id é válido.";
                }
            }
        } else {
            $message = 'Erros no formulário:<ul><li>' . implode('</li><li>', array_map('esc_html', $errors)) . '</li></ul>';
        }
    }

    // List tasks
    $tasks = listTasks($apiBase, $token, $projectId);

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
?>