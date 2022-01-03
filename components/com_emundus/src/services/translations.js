import client from './axiosClient';

export default {
  async getDefaultLanguage() {
    try {
      const response = await client().get('index.php?option=com_emundus&controller=translations&task=getdefaultlanguage');

      return response.data;
    } catch (e) {
      return false;
    }
  },

  async updateLanguage(lang_code,published,default_lang = 0) {
    try {
      const formData = new FormData();
      formData.append('published', published);
      formData.append('lang_code', lang_code);
      formData.append('default_lang', default_lang);

      return await client().post(`index.php?option=com_emundus&controller=translations&task=updatelanguage`,
          formData,
          {
            headers: {
              'Content-Type': 'multipart/form-data'
            }
          }
      );
    } catch (e) {
      return {
        status: false,
        msg: e.message
      }
    }
  },

  async getObjects() {
    try {
      return await client().get(`index.php?option=com_emundus&controller=translations&task=gettranslationsobjects`);
    } catch (e) {
      return {
        status: false,
        msg: e.message
      }
    }
  },

  async getDatas(table,reference_id,label,filters) {
    try {
      return await client().get(`index.php?option=com_emundus&controller=translations&task=getdatas`, {
        params: {
          table,
          reference_id,
          label,
          filters,
        }
      });
    } catch (e) {
      return {
        status: false,
        msg: e.message
      }
    }
  },

  async getTranslations(type,default_lang,lang_to,reference_id,fields,reference_table = ''){
    switch(type){
      case 'falang':
        try {
          return await client().get(`index.php?option=com_emundus&controller=translations&task=getfalangtranslations`, {
            params: {
              default_lang,
              lang_to,
              reference_table,
              reference_id,
              fields,
            }
          });
        } catch (e) {
          return {
            status: false,
            msg: e.message
          }
        }
        break;
      default:
    }
  }
};
