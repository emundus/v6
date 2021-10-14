import client from './axiosClient';

export default {
  async getAttachments(user) {
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

      return response.data;
    } catch (e) {
      throw e;
    }
  },
  
  async deleteAttachment(attachment_id) {
    try {
      const response = await client().get('index.php?option=com_emundus&controller=application&task=deleteattachment', {
        params: {
          attachment_id: attachment_id,
        }
      });

      return response.data;
    } catch (e) {
      throw e;
    }
  }
};
