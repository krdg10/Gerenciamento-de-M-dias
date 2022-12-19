import { defineRule, configure } from 'vee-validate';
//import { required, email, numeric, digits, max, min, min_value, max_value, integer, regex, required_if } from '@vee-validate/rules';
import { required, max, numeric, min_value, digits, email, min } from '@vee-validate/rules';
import pt_BR from '@vee-validate/i18n/dist/locale/pt_BR.json';
import { localize } from '@vee-validate/i18n';


defineRule('required', required);
defineRule('max', max);
defineRule('min', min);
defineRule('numeric', numeric);
defineRule('min_value', min_value);
defineRule('digits', digits);
defineRule('email', email);




configure({
  generateMessage: localize({
    pt_BR,
  }),
});


/*configure({
  generateMessage: localize('pt_BR', {
    messages: {
      required: 'Esse campo é obrigatório.',
      email: 'Digite email válido.',
      numeric: 'Digite apenas números.',
      min: 'Digite 0:{length} caracteres.',
      max: 'Digite no máximo 0:{length} caracteres.',
      digits: 'O {field} precisa de 0:{length} caracteres.', // length pra funcionar a variavel tem que ser desse jeito, field tbm. o max e min n sei como seria
      min_value: 'O valor mínimo é {min}.',
      integer: 'O valor não pode ser fracionário.',
      max_value: 'O valor máximo é {max}.',
      regex: 'Valor inválido.',
      required_if: 'Esse campo é obrigatório.'
    }
  }),
});*/