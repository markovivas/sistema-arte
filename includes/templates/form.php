<?php
function sistema_arte_form_template($message) {
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

        <form method="POST" class="space-y-4" id="sistema-arte-form">
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
                           <option value="SARH" <?php echo (isset($_POST['department']) && $_POST['department'] == 'SARH') ? 'selected' : ''; ?>>SARH</option>
    <option value="SEMAP" <?php echo (isset($_POST['department']) && $_POST['department'] == 'SEMAP') ? 'selected' : ''; ?>>SEMAP</option>
    <option value="SECOM" <?php echo (isset($_POST['department']) && $_POST['department'] == 'SECOM') ? 'selected' : ''; ?>>SECOM</option>
    <option value="SEMCI" <?php echo (isset($_POST['department']) && $_POST['department'] == 'SEMCI') ? 'selected' : ''; ?>>SEMCI</option>
    <option value="SEDESO" <?php echo (isset($_POST['department']) && $_POST['department'] == 'SEDESO') ? 'selected' : ''; ?>>SEDESO</option>
    <option value="SEDUC" <?php echo (isset($_POST['department']) && $_POST['department'] == 'SEDUC') ? 'selected' : ''; ?>>SEDUC</option>
    <option value="SEFIN" <?php echo (isset($_POST['department']) && $_POST['department'] == 'SEFIN') ? 'selected' : ''; ?>>SEFIN</option>
    <option value="SEGOV" <?php echo (isset($_POST['department']) && $_POST['department'] == 'SEGOV') ? 'selected' : ''; ?>>SEGOV</option>
    <option value="SEMMA" <?php echo (isset($_POST['department']) && $_POST['department'] == 'SEMMA') ? 'selected' : ''; ?>>SEMMA</option>
    <option value="SEMOSP" <?php echo (isset($_POST['department']) && $_POST['department'] == 'SEMOSP') ? 'selected' : ''; ?>>SEMOSP</option>
    <option value="SEPLAN" <?php echo (isset($_POST['department']) && $_POST['department'] == 'SEPLAN') ? 'selected' : ''; ?>>SEPLAN</option>
    <option value="PGM" <?php echo (isset($_POST['department']) && $_POST['department'] == 'PGM') ? 'selected' : ''; ?>>PGM</option>
    <option value="SEMS" <?php echo (isset($_POST['department']) && $_POST['department'] == 'SEMS') ? 'selected' : ''; ?>>SEMS</option>
    <option value="SESP" <?php echo (isset($_POST['department']) && $_POST['department'] == 'SESP') ? 'selected' : ''; ?>>SESP</option>
    <option value="SELTC" <?php echo (isset($_POST['department']) && $_POST['department'] == 'SELTC') ? 'selected' : ''; ?>>SELTC</option>
    <option value="SEDEC" <?php echo (isset($_POST['department']) && $_POST['department'] == 'SEDEC') ? 'selected' : ''; ?>>SEDEC</option>
    <option value="SEMOB" <?php echo (isset($_POST['department']) && $_POST['department'] == 'SEMOB') ? 'selected' : ''; ?>>SEMOB</option>
    <option value="OUTRAS" <?php echo (isset($_POST['department']) && $_POST['department'] == 'OUTRAS') ? 'selected' : ''; ?>>OUTRAS</option>
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

            <!-- Descrição (campo original, agora oculto) -->
            <input type="hidden" id="description" name="description">

            <!-- Campo para detalhes adicionais -->
            <div>
                <label for="additional_info" class="block text-sm font-medium text-gray-700 mb-1">Detalhes da Solicitação *</label>
                <textarea id="additional_info" name="additional_info" rows="4"
                          class="mt-1 block w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm"
                          required><?php echo esc_textarea(isset($_POST['additional_info']) ? wp_unslash($_POST['additional_info']) : ''); ?></textarea>
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
                        <option value="1" <?php echo (isset($_POST['priority']) && $_POST['priority'] == '1') ? 'selected' : ''; ?>>Alta</option>
                        <option value="2" <?php echo (isset($_POST['priority']) && $_POST['priority'] == '2') ? 'selected' : ''; ?>>Média-Alta</option>
                        <option value="3" <?php echo (!isset($_POST['priority']) || $_POST['priority'] == '3') ? 'selected' : ''; ?>>Média</option>
                        <option value="4" <?php echo (isset($_POST['priority']) && $_POST['priority'] == '4') ? 'selected' : ''; ?>>Baixa</option>
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