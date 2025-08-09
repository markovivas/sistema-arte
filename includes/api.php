<?php
// Função genérica para fazer requisições à API
function makeApiRequest($url, $token, $method = 'GET', $data = null) {
    $args = [
        'headers' => [
            'Authorization' => 'Bearer ' . sanitize_text_field($token),
            'Content-Type' => 'application/json',
        ],
        'method' => $method,
        'timeout' => 30,
    ];

    if ($data && in_array($method, ['PUT', 'POST'])) {
        $args['body'] = wp_json_encode($data);
        if ($args['body'] === false) {
            error_log('Sistema Arte: Falha ao codificar JSON para a requisição API');
            return [
                'success' => false,
                'error' => 'Erro interno: Falha ao codificar dados da requisição.',
                'http_code' => 0
            ];
        }
    }

    $response = wp_remote_request($url, $args);
    
    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        error_log('Sistema Arte: Erro na requisição API: ' . $error_message);
        return [
            'success' => false,
            'error' => 'Erro na requisição: ' . esc_html($error_message),
            'http_code' => 0
        ];
    }

    $http_code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    
    $result = json_decode($body, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('Sistema Arte: Erro ao decodificar JSON: ' . json_last_error_msg());
        return [
            'success' => false,
            'error' => 'Erro ao processar resposta da API.',
            'http_code' => $http_code,
            'raw_response' => $body
        ];
    }

    if ($http_code >= 200 && $http_code < 300) {
        return ['success' => true, 'data' => $result, 'http_code' => $http_code];
    } else {
        $error = isset($result['message']) ? $result['message'] : $body;
        error_log('Sistema Arte: Erro na API (HTTP ' . $http_code . '): ' . $error);
        return [
            'success' => false,
            'error' => esc_html($error),
            'http_code' => $http_code,
            'raw_response' => $body
        ];
    }
}

// Função para listar tarefas do projeto
function listTasks($apiBase, $token, $projectId) {
    $url = rtrim($apiBase, '/') . '/projects/' . absint($projectId) . '/tasks';
    $response = makeApiRequest($url, $token);

    if ($response['success']) {
        return $response['data'];
    } else {
        error_log('Sistema Arte: Erro ao listar tarefas: ' . $response['error']);
        return false;
    }
}

// Função para adicionar tarefa
function addTask($apiBase, $token, $task, $projectId) {
    // Validação básica
    if (empty($task['title']) || empty($task['project_id'])) {
        error_log('Sistema Arte: Título ou project_id ausente na tarefa.');
        return ['success' => false, 'error' => 'Título e project_id são obrigatórios.'];
    }
    if ($task['project_id'] !== $projectId) {
        error_log('Sistema Arte: project_id mismatch: ' . $task['project_id'] . ' != ' . $projectId);
        return [
            'success' => false,
            'error' => "project_id no corpo ({$task['project_id']}) deve corresponder ao ID do projeto no caminho ({$projectId})."
        ];
    }

    $url = rtrim($apiBase, '/') . '/projects/' . absint($projectId) . '/tasks';
    $response = makeApiRequest($url, $token, 'PUT', $task);

    return $response;
}
?>