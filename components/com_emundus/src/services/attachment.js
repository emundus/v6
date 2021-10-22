import client from './axiosClient';

export default {
  async getAttachmentsByUser(user) {
    try {
      const response = await client().get('index.php?option=com_emundus&controller=application&task=getuserattachments', {
        params: {
          user_id: user,
        }
      });

      return response.data;
    } catch (e) {
      throw e;
    }
  },

  async getAttachmentsByFnum(fnum) {
    try {
      const response = await client().get('index.php?option=com_emundus&controller=application&task=getattachmentsbyfnum', {
        params: {
          fnum : fnum,
        }
      });

      // add show attribute to true to all attchments in response data
      response.data.forEach(attachment => {
        attachment.show = true;
        attachment.is_validated = attachment.is_validated === null ? "0" : attachment.is_validated;
      });

      return response.data;
    } catch (e) {
      throw e;
    }
  },

  async deleteAttachments(fnum, attachment_ids) {
    try {
      const response = await client().post('index.php?option=com_emundus&controller=application&task=deleteattachement', {
        params: {
          fnum: fnum,
          ids: JSON.stringify(attachment_ids),
        }
      });

      return response;
    } catch (e) {
      throw e;
    }
  },
  
  async updateAttachment(formData) {
    try {
      const response = await client().post('index.php?option=com_emundus&controller=application&task=updateattachment', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        }
      );

      return response.data;
    } catch (e) {
      throw e;
    }
  },

  async getPreview(user, filename) {
    try {
      const response = await client().get('index.php?option=com_emundus&controller=application&task=getattachmentpreview', {
        params: {
          user: user,
          filename: filename,
        }
      });

      return response.data;
    } catch (e) {
      throw e;
    }
  }
};
