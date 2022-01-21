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
      return {
        status: false,
        msg: e.message
      }
    }
  },

  async getAttachmentsByFnum(fnum) {
    try {
      const response = await client().get('index.php?option=com_emundus&controller=application&task=getattachmentsbyfnum', {
        params: {
          fnum: fnum,
        }
      });


      if (response.data.status) {
        // add show attribute to true to all attchments in response data

        if (typeof response.data.attachments === 'string') {
          response.data.attachments = JSON.parse(response.data.attachments);
        }

        if (typeof response.data.attachments === 'object') {
          // cast object to array of objects
          response.data.attachments = Object.values(response.data.attachments);
        }

        response.data.attachments.forEach(attachment => {
          if (attachment.is_validated === null) {
            attachment.is_validated = -2;
          }

          attachment.show = true;
        });
      }

      return response.data;
    } catch (e) {
      return {
        status: false,
        msg: e.message
      }
    }
  },

  async getAttachmentCategories() {
    try {
      const response = await client().get('index.php?option=com_emundus&controller=files&task=getattachmentcategories');

      return response.data;
    } catch (e) {
      return {
        status: false,
        msg: e.message
      }
    }
  },

  async deleteAttachments(fnum, student_id, attachment_ids) {
    try {
      const formData = new FormData();
      formData.append('ids', JSON.stringify(attachment_ids));

      return await client().post(`index.php?option=com_emundus&controller=application&task=deleteattachement&fnum=${fnum}&student_id=${student_id}`,
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

  async updateAttachment(formData) {
    try {
      const response = await client().post('index.php?option=com_emundus&controller=application&task=updateattachment', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      });

      return response.data;
    } catch (e) {
      return {
        status: false,
        msg: e.message
      }
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
      return {
        status: false,
        msg: e.message
      }
    }
  },
  exportAttachments(student, fnum, attachment_ids) {
    const formData = new FormData();
    formData.append('student_id', student);
    formData.append('fnum', fnum);
    attachment_ids.forEach(id => {
      formData.append('ids[]', id);
    });

    return client().post('index.php?option=com_emundus&controller=application&task=exportpdf', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
  }
};
