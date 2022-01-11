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


      if (typeof response.data === "string") {
        response.data = JSON.parse(response.data);
      }

      if (typeof response.data === "object") {
        // object to array containing only the attachments
        response.data = Object.values(response.data);
      }
      
      // add show attribute to true to all attchments in response data
      if (response.data) {
        response.data.forEach(attachment => {
          attachment.show = true;
        });
      }

      return response.data;
    } catch (e) {
      return false;
    }
  },

  async getAttachmentCategories() {
    try {
      const response = await client().get('index.php?option=com_emundus&controller=files&task=getattachmentcategories');

      return response.data;
    } catch (e) {
      throw e;
    }
  },

  async deleteAttachments(fnum, student_id, attachment_ids) {
    try {
      const formData = new FormData();
      formData.append('ids', JSON.stringify(attachment_ids));
      
      const response = await client().post(`index.php?option=com_emundus&controller=application&task=deleteattachement&fnum=${fnum}&student_id=${student_id}`, formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        }
      );

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
