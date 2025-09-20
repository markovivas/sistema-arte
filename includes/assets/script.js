jQuery(document).ready(function($) {
    var $form = $('#sistema-arte-form');
    if ($form.length) {
        // Adiciona máscara ao campo de telefone
        var phoneInput = $('#phone');
        if (phoneInput.length && typeof phoneInput.mask === 'function') {
            var maskBehavior = function (val) {
                return val.replace(/\D/g, '').length === 11 ? '(00) 00000-0000' : '(00) 0000-00009';
            },
            options = {onKeyPress: function(val, e, field, options) {
                    field.mask(maskBehavior.apply({}, arguments), options);
                }
            };
            phoneInput.mask(maskBehavior, options);
        }

        $form.on('submit', function() {
            return true;
        });
    } else {
        console.warn('Sistema Arte: Formulário #sistema-arte-form não encontrado.');
    }
});