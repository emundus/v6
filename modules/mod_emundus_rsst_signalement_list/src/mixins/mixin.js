import moment from 'moment';

export default {
    methods: {
        formattedDate: function (date = '', format = 'LLLL') {
            let formattedDate = '';

            if (date !== null) {
                if (date !== '') {
                    formattedDate = moment(date).format(format);
                } else {
                    formattedDate = moment().format(format);
                }
            }

            return formattedDate;
        },
        texteFromValue(val) {
            let texte = '';
            switch (val) {
                case 'a_faire':
                    texte = 'À faire';
                    break;
                case 'en_cours':
                    texte = 'En cours';
                    break;
                case 'fait' :
                    texte = 'Fait';
                    break;
                case 'sans_objet' :
                    texte = 'Sans objet';
                    break;
                case '1' :
                    texte = 'Publié';
                    break;
                case '0' :
                    texte = 'Non publié';
                    break;
                default:
                    texte = val;
            }
            return texte;
        },
    }
};

