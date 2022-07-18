import moment from 'moment';
import userService from '../services/user.js';
import attachmentService from '../services/attachment.js';

import mimeTypes from '../data/mimeTypes';

var mixin = {
	methods: {
		formattedDate: function (date = '',format = 'LLLL') {
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
		strippedHtml: function (html) {
			if (html === null || html === undefined) {
				return '';
			}

			return html.replace(/<(?:.|\n)*?>/gm, '');
		},
		getUserNameById: function (id) {
			let completeName = '';
			// id to int
			id = parseInt(id);

			if (id > 0) {
				const user = this.$store.state.user.users[id];
				if (user) {
					completeName = user.firstname + ' ' + user.lastname;
				} else {
					userService.getUserById(id).then(data => {
						if (data.status && data.user[0]) {
							completeName = data.user[0].firstname + ' ' + data.user[0].lastname;
							data.user[0].id = id;
							this.$store.dispatch('user/setUsers', data.user);
						}
					});
				}
			}

			return completeName;
		},
		async getAttachmentCategories() {
			const response = await attachmentService.getAttachmentCategories();

			if (response.status === true) {
				// translate categories values
				Object.entries(response.categories).forEach(([key, value]) => {
					response.categories[key] = this.translate(value);
				});

				// remove empty categories
				delete response.categories[''];

				this.$store.dispatch('attachment/setCategories', response.categories);

				return response.categories;
			} else {
				return {};
			}
		},
		async asyncForEach(array, callback) {
			for (let index = 0; index < array.length; index++) {
				await callback(array[index], index, array);
			}
		},
		getMimeTypeFromExtension(extension){
			if (mimeTypes.mimeTypes.hasOwnProperty(extension)) {
				return mimeTypes.mimeTypes[extension];
			}
			return false;
		}
	}
};

export default mixin;
