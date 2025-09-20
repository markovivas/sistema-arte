jQuery(document).ready(function($) {
    const board = $('#kanban-board');
    if (board.length === 0) {
        return;
    }

    // Função para inicializar o 'sortable' (arrastar e soltar)
    $('.kanban-column-body').sortable({
        connectWith: ".kanban-column-body", // Permite arrastar entre colunas
        placeholder: "kanban-card-placeholder", // Estilo do espaço reservado
        opacity: 0.8, // Deixa o card semitransparente ao arrastar
        revert: 200, // Animação suave ao soltar
        start: function(event, ui) {
            // Garante que o placeholder tenha a mesma altura do card arrastado
            ui.placeholder.height(ui.item.outerHeight());
        },
        // Função chamada quando um card é solto em uma nova coluna
        receive: function(event, ui) {
            const postId = ui.item.data('post-id');
            const newStatus = $(this).data('status-slug');
            const originalFooterHTML = ui.item.find('.kanban-card-footer').html(); // Salva o estado original

            // --- ATUALIZAÇÃO OTIMISTA ---
            // 1. Atualiza a UI imediatamente para parecer instantâneo.
            // Adiciona uma classe para um feedback visual sutil.
            ui.item.addClass('kanban-card-saving');

            // 2. Envia a atualização para o WordPress em segundo plano.
            $.ajax({
                url: kanban_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'update_demand_status', // Ação do WordPress
                    nonce: kanban_ajax.nonce,
                    post_id: postId,
                    new_status: newStatus
                },
                success: function(response) {
                    if (response.success) {
                        // Sucesso! A UI já está correta. Apenas damos um feedback de sucesso.
                        ui.item.addClass('kanban-card-success');
                        setTimeout(function() {
                            ui.item.removeClass('kanban-card-success');
                        }, 1500);
                    } else {
                        // Erro! Desfaz a alteração na UI e avisa o usuário.
                        $(ui.sender).sortable('cancel');
                        ui.item.find('.kanban-card-footer').html(originalFooterHTML); // Restaura o rodapé
                        alert('Erro ao atualizar o status: ' + (response.data.message || 'Tente novamente.'));
                    }
                },
                error: function() {
                    // Erro de conexão! Também desfaz a alteração.
                    $(ui.sender).sortable('cancel');
                    ui.item.find('.kanban-card-footer').html(originalFooterHTML); // Restaura o rodapé
                    alert('Erro de comunicação. Tente novamente.');
                },
                complete: function() {
                    // Independentemente do resultado, remove a classe de "salvando".
                    ui.item.removeClass('kanban-card-saving');
                }
            });
        }
    }).disableSelection();
});