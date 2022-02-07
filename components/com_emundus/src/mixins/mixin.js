import moment from 'moment';
import userService from '../services/user.js';
import attachmentService from '../services/attachment.js';

var mixin = {
	methods: {
		formattedDate: function (date = '',format = 'LLLL') {
			if(date !== '') {
				return moment(date).format(format);
			} else {
				return moment().format(format);
			}
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
				delete response.categories[""];

				this.$store.dispatch('attachment/setCategories', response.categories);
				return this.$store.state.attachment.categories;
			} else {
				return {};
			}
		},
		async asyncForEach(array, callback) {
			for (let index = 0; index < array.length; index++) {
				await callback(array[index], index, array);
			}
		},
	}
};

export default mixin;
