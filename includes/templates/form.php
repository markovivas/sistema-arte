<?php
function sistema_arte_form_template($message) {
    // Centralizar opções para facilitar a manutenção
    $departments = [
        'SARH' => 'SARH', 'SEMAP' => 'SEMAP', 'SECOM' => 'SECOM', 'SEMCI' => 'SEMCI',
        'SEDESO' => 'SEDESO', 'SEDUC' => 'SEDUC', 'SEFIN' => 'SEFIN', 'SEGOV' => 'SEGOV',
        'SEMMA' => 'SEMMA', 'SEMOSP' => 'SEMOSP', 'SEPLAN' => 'SEPLAN', 'PGM' => 'PGM',
        'SEMS' => 'SEMS', 'SESP' => 'SESP', 'SELTC' => 'SELTC', 'SEDEC' => 'SEDEC',
        'SEMOB' => 'SEMOB', 'OUTRAS' => 'OUTRAS'
    ];

    $priorities = [
        '1' => 'Alta',
        '2' => 'Média-Alta',
        '3' => 'Média',
        '4' => 'Baixa'
    ];

    // Valores atuais para manter no formulário em caso de erro
    $current_department = isset($_POST['department']) ? wp_unslash($_POST['department']) : '';
    $current_priority = isset($_POST['priority']) ? wp_unslash($_POST['priority']) : '3'; // Padrão é 'Média'

    // Calcular data padrão (hoje + 7 dias) no formato YYYY-MM-DDTHH:MM
    $default_due_date = '';
    try {
        $date = new DateTime();
        $date->add(new DateInterval('P7D'));
        $date->setTime(17, 0); // Define para 17:00
        $default_due_date = $date->format('Y-m-d\TH:i');
    } catch (Exception $e) {
        error_log('Erro ao calcular data padrão: ' . $e->getMessage());
    }
    
    // Verificar se já existe um valor POST para manter na reexibição do formulário
    $due_date_value = isset($_POST['due_date']) ? esc_attr(wp_unslash($_POST['due_date'])) : $default_due_date;
    ?>
    <!-- Coluna Esquerda - Formulário -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-6 border-b pb-2">Adicionar uma nova demanda</h2>

        <?php if ($message): ?>
            <div class="p-4 mb-6 rounded-lg <?php echo strpos($message, 'sucesso') !== false ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                <?php echo wp_kses_post($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4" id="sistema-arte-form" enctype="multipart/form-data">
            <?php wp_nonce_field('sistema_arte_nonce', 'sistema_arte_nonce'); ?>
            
            <!-- Campo Título (obrigatório) -->
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Título da Arte*</label>
                <input type="text" id="title" name="title" required
                       class="mt-1 block w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm"
                       value="<?php echo esc_attr(isset($_POST['title']) ? wp_unslash($_POST['title']) : ''); ?>">
            </div>

            <!-- Novos campos para informações do usuário -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Nome Completo -->
                <div>
                    <label for="full_name" class="block text-sm font-medium text-gray-700 mb-1">Seu nome completo *</label>
                    <input type="text" id="full_name" name="full_name" required
                           class="mt-1 block w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm"
                           value="<?php echo esc_attr(isset($_POST['full_name']) ? wp_unslash($_POST['full_name']) : ''); ?>">
                </div>

                <!-- Secretaria -->
                <div>
                    <label for="department" class="block text-sm font-medium text-gray-700 mb-1">Secretaria *</label>
                    <select id="department" name="department" required
                            class="mt-1 block w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                        <option value="">Selecione...</option>
                        <?php foreach ($departments as $value => $label): ?>
                            <option value="<?php echo esc_attr($value); ?>" <?php selected($current_department, $value); ?>>
                                <?php echo esc_html($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Telefone/WhatsApp -->
            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Telefone/WhatsApp *</label>
                <input type="tel" id="phone" name="phone" required
                       class="mt-1 block w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm"
                       value="<?php echo esc_attr(isset($_POST['phone']) ? wp_unslash($_POST['phone']) : ''); ?>"
                       placeholder="(99) 99999-9999">
            </div>

            <!-- O campo 'description' oculto não é mais necessário -->

            <!-- Campo para detalhes adicionais -->
            <div>
                <label for="additional_info" class="block text-sm font-medium text-gray-700 mb-1">Detalhes da Solicitação *</label>
                <textarea id="additional_info" name="additional_info" rows="4"
                          class="mt-1 block w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm"
                          required><?php echo esc_textarea(isset($_POST['additional_info']) ? wp_unslash($_POST['additional_info']) : ''); ?></textarea>
            </div>

            <!-- Campo de Anexo -->
            <div class="relative">
                <label for="attachment" class="block text-sm font-medium text-gray-700 mb-1">Anexar Arquivos (opcional)</label>
                <div class="mt-1 flex items-center">
                    <label for="attachment" class="w-full cursor-pointer bg-white rounded-md border border-gray-300 p-3 shadow-sm flex items-center justify-between hover:border-indigo-500">
                        <span class="text-gray-500" id="attachment-label">Nenhum arquivo selecionado</span>
                        <span class="px-4 py-1.5 text-sm font-semibold text-indigo-700 bg-indigo-100 rounded-full hover:bg-indigo-200">Escolher arquivo</span>
                    </label>
                    <input type="file" id="attachment" name="attachment" class="sr-only" onchange="document.getElementById('attachment-label').textContent = this.files[0] ? this.files[0].name : 'Nenhum arquivo selecionado';">
                </div>
            </div>

            <!-- NOVOS CAMPOS VISÍVEIS: Data de Entrega e Prioridade -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Data de Entrega -->
                <div>
                    <label for="due_date" class="block text-sm font-medium text-gray-700 mb-1">Data de Entrega</label>
                    <input type="datetime-local" id="due_date" name="due_date"
                           class="mt-1 block w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm"
                           value="<?php echo $due_date_value; ?>">
                </div>

                <!-- Prioridade -->
                <div>
                    <label for="priority" class="block text-sm font-medium text-gray-700 mb-1">Prioridade</label>
                    <select id="priority" name="priority"
                            class="mt-1 block w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                        <?php foreach ($priorities as $value => $label): ?>
                            <option value="<?php echo esc_attr($value); ?>" <?php selected($current_priority, $value); ?>>
                                <?php echo esc_html($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Botão de envio -->
            <button type="submit"
                    class="w-full p-3 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors duration-200 shadow">
                Enviar Solicitação
            </button>
        </form>
    </div>
    <?php
}
?>