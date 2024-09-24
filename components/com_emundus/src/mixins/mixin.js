import moment from 'moment';
import userService from '../services/user.js';
import attachmentService from '../services/attachment.js';

import mimeTypes from '../data/mimeTypes';

var mixin = {
  data: function () {
    return {
      timer: null,
    };
  },
  methods: {
    /**
		 * Format date
		 * @param date
		 * @param format
		 * @param utc
		 * @param local, if true, the date will not be converted using utc offset
		 * @returns {string}
		 */
    formattedDate: function (date = '',format = 'LLLL', utc = null) {
      let formattedDate = '';

      if (date !== null) {
        if (utc === null) {
          utc = this.$store.state.global.offset;
        }

        if (date !== '') {
          let year = date.substring(0, 4);
          let month = date.substring(5, 7);
          let day = date.substring(8, 10);
          let hour = date.substring(11, 13);
          let minute = date.substring(14, 16);
          let second = date.substring(17, 19);
          const stringDate = year + '-' + month + '-' + day + 'T' + hour + ':' + minute + ':' + second + '+00:00';

          formattedDate = moment(stringDate).utcOffset(utc).format(format);
        } else {
          formattedDate = moment().utcOffset(utc).format(format);
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
      id = parseInt(id);

      if (id > 0) {
        const user = this.$store.state.user.users[id];
        if (user) {
          completeName = user.firstname + ' ' + user.lastname;
        } else {
          userService.getUserNameById(id).then(data => {
            if (data.status && data.user.user_id == id) {
              completeName = data.user.firstname + ' ' + data.user.lastname;
              this.$store.dispatch('user/setUsers', [data.user]);
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
    },
    checkMaxMinlength(event, maxlength, minlength = null) {
      if (event.target.textContent.length >= maxlength && event.keyCode != 8) {
        event.preventDefault();
      }
      if (minlength !== null) {
        if (event.target.textContent.length <= minlength && event.keyCode == 8) {
          event.preventDefault();
        }
      }
    },
    differencesBetweenObjetcs(obj1, obj2, propsToCompare = null) {
      let differences = [];

      if (propsToCompare === null) {
        const props1 = Object.getOwnPropertyNames(obj1);
        const props2 = Object.getOwnPropertyNames(obj2);

        propsToCompare = Array.from(new Set(props1.concat(props2)));
      }

      propsToCompare = propsToCompare.filter((prop) => {
        return prop !== '__ob__';
      });

      propsToCompare.forEach((prop) => {
        if (typeof obj1[prop] === undefined || typeof obj2[prop] === undefined) {
          differences.push(prop);
        } else if (obj1[prop] != obj2[prop]) {
          if (typeof obj1[prop] != 'object' ||
						(typeof obj1[prop] == 'object' && JSON.stringify(obj1[prop]) !== JSON.stringify(obj2[prop]))) {
            differences.push(prop);
          }
        }
      });

      return differences;
    },
    tip(group, text = "", title = "Information") {
      this.$notify({
        group,
        title: `${title}`,
        text: text,
        duration: 3000
      });
    },
    debounce(fn, wait){
      return (...args) => {
        if(this.timer) {
          clearTimeout(this.timer); // clear any pre-existing timer
        }
        const context = this; // get the current context
		  this.timer = setTimeout(()=>{
          fn.apply(context, args); // call the function if time expires
        }, wait);
      };
    },
      /**
       * Highlight the search term in the elements by wrapping it in a span with a background color
       * @param searchTerm
       * @param elementsToSearchIn
       */
      highlight(searchTerm, elementsToSearchIn = []) {
          if (elementsToSearchIn.length > 0) {
              let elements = [];
              elementsToSearchIn.forEach((elementClass) => {
                  elements = elements.concat(Array.from(document.querySelectorAll(elementClass)));
              });

              elements.forEach((element) => {
                  const text = element.innerText;
                  let regex = new RegExp(`(${searchTerm})`, "gi");
                  // Check if the element's text contains the search term
                  if (searchTerm && text.match(regex)) {
                      // Split the text into parts (matched and unmatched)
                      const parts = text.split(regex);
                      // Create a new HTML structure with the matched term highlighted
                      const highlightedText = parts
                          .map((part) =>
                              part.match(regex)
                                  ? `<span style="background-color: var(--em-yellow-1);">${part}</span>`
                                  : part
                          )
                          .join("");
                      // Replace the original text with the highlighted version
                      element.innerHTML = highlightedText;
                  } else {
                      element.innerHTML = text;
                  }
              });
          }
      },
  }
};

export default mixin;
