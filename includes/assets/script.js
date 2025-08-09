jQuery(document).ready(function($) {
    var $form = $('#sistema-arte-form');
    if ($form.length) {
        // Definir data padrão (hoje + 7 dias) no carregamento
        var today = new Date();
        today.setDate(today.getDate() + 7);
        today.setHours(17, 0, 0, 0); // Define para 17:00
        
        // Formata para o formato esperado pelo input datetime-local (YYYY-MM-DDTHH:MM)
        var formattedDate = today.toISOString().slice(0, 16);
        
        // Define o valor do campo se ele estiver vazio
        if (!$('#due_date').val()) {
            $('#due_date').val(formattedDate);
        }	
        $form.on('submit', function(e) {
            // Coleta todos os dados dos novos campos
            var fullName = $('#full_name').val() || '';
            var department = $('#department').val() || '';
            var phone = $('#phone').val() || '';
            var additionalInfo = $('#additional_info').val() || '';
            
            // Formata a descrição com HTML
            var formattedDescription = 
                "<div style='font-family: Arial, sans-serif;'>" +
                "<h3 style='color: #2563eb; border-bottom: 1px solid #ddd; padding-bottom: 5px;'>SOLICITAÇÃO</h3>" +
                "<ul style='list-style-type: none; padding-left: 0;'>" +
                "<li><strong>Solicitante:</strong> " + fullName.replace(/</g, '&lt;').replace(/>/g, '&gt;') + "</li>" +
                "<li><strong>Secretaria:</strong> " + department.replace(/</g, '&lt;').replace(/>/g, '&gt;') + "</li>" +
                "<li><strong>Contato:</strong> " + phone.replace(/</g, '&lt;').replace(/>/g, '&gt;') + "</li>" +
                "</ul>" +
                "<h3 style='color: #2563eb; border-bottom: 1px solid #ddd; padding-bottom: 5px;'>DETALHES</h3>" +
                "<p style='white-space: pre-line;'>" + additionalInfo.replace(/</g, '&lt;').replace(/>/g, '&gt;') + "</p>" +
                "</div>";
            
            // Atribui ao campo de descrição que será enviado ao backend
            $('#description').val(formattedDescription);
            
            // Define valores padrão para os campos originais se necessário
            if (!$('#due_date').val()) {
                var today = new Date();
                today.setDate(today.getDate() + 7);
                today.setHours(17, 0);
                $('#due_date').val(today.toISOString().slice(0, 16));
            }
            
            if (!$('#priority').val()) {
                $('#priority').val(3); // Prioridade média por padrão
            }
            
            return true;
        });
    } else {
        console.warn('Sistema Arte: Formulário #sistema-arte-form não encontrado.');
    }
});