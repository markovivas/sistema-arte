<?php
function sistema_arte_tasks_template($tasks) {
    ?>
    <!-- Coluna Direita - Lista de Tarefas -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-6 border-b pb-2">Demandas Pendentes</h2>
        <?php if ($tasks): ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="p-3 border-b font-semibold text-gray-700">ID</th>
                            <th class="p-3 border-b font-semibold text-gray-700">Título</th>
                            <th class="p-3 border-b font-semibold text-gray-700">Vencimento</th>
                            <th class="p-3 border-b font-semibold text-gray-700">Prioridade</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tasks as $task): ?>
                            <tr class="border-b hover:bg-gray-50 transition-colors duration-150">
                                <td class="p-3 text-gray-600"><?php echo esc_html($task['id']); ?></td>
                                <td class="p-3 font-medium text-gray-800"><?php echo esc_html($task['title']); ?></td>
                                <td class="p-3 text-gray-600">
                                    <?php
                                    if ($task['due_date']) {
                                        try {
                                            $date = new DateTime($task['due_date']);
                                            echo esc_html($date->format('d/m/Y H:i'));
                                        } catch (Exception $e) {
                                            echo '-';
                                        }
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                                <td class="p-3">
                                    <?php if ($task['priority']): ?>
                                        <span class="px-2 py-1 rounded-full text-xs font-medium 
                                            <?php echo esc_attr($task['priority'] <= 2 ? 'bg-red-100 text-red-800' : 
                                                  ($task['priority'] <= 3 ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800')); ?>">
                                            <?php echo esc_html($task['priority']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-8">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <p class="mt-4 text-gray-600">Nenhuma tarefa encontrada ou erro ao carregar.</p>
            </div>
        <?php endif; ?>
    </div>
    <?php
}
?>