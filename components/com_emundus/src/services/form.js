import client from './axiosClient';

export default {
    async updateFormLabel(params) {
        const formData = new FormData();
        Object.keys(params).forEach(key => formData.append(key, params[key]));

        try {
            const response = await client().post('index.php?option=com_emundus&controller=form&task=updateformlabel',
                formData
            );

            return response;
        } catch (error) {
            return {
                status: false,
                error:error
            };
        }
    },
    async getFilesByForm(id) {
        try {
            const response = await client().get(
                'index.php?option=com_emundus&controller=form&task=getfilesbyform&pid=' + id
            );

            return response;
        } catch (error) {
            return {
                status: false,
                error:error
            };
        }
    },
    async getSubmissionPage(id) {
        try {
            const response = await client().get(
                'index.php?option=com_emundus&controller=form&task=getsubmittionpage',
                {
                    params: {
                        prid: id
                    }
                }
            );

            return response;
        } catch (error) {
            return {
                status: false,
                error:error
            };
        }
    },
    async getFormsByProfileId(id)
    {
        try {
            const response = await client().get(
                'index.php?option=com_emundus&controller=form&task=getFormsByProfileId',
                {
                    params: {
                        profile_id: id
                    }
                }
             );

            return response;
        } catch (error) {
            return {
                status: false,
                error:error
            };
        }
    },
    async getProfileLabelByProfileId(id)
    {
        try {
            const response = await client().get(
                'index.php?option=com_emundus&controller=form&task=getProfileLabelByProfileId',
                {
                    params: {
                        profile_id: id
                    }
                }
            );

            return response;
        } catch (error) {
            return {
                status: false,
                error:error
            };
        }
    },
    async getDocuments(id)
    {
        try {
            const response = await client().get(
                'index.php?option=com_emundus&controller=form&task=getDocuments',
                {
                    params: {
                        pid: id
                    }
                }
             );

            return response;
        } catch (error) {
            return {
                status: false,
                error: error
            };
        }
    },
    async reorderDocuments(documents)
    {
        try {
            const formData = new FormData();
            formData.append('documents', JSON.stringify(documents));

            const response = await client().post(
                'index.php?option=com_emundus&controller=form&task=reorderDocuments',
                documents
            );

            return response;
        } catch (error) {
            return {
                status: false,
                error:error
            };
        }
    },
    async getAssociatedCampaigns(id)
    {
      try {
        const response = client().get(
          'index.php?option=com_emundus&controller=form&task=getassociatedcampaign',
          {
            params: {
              pid: id
            }
          }
        );

        return response;
      } catch (error) {
        return {
          status: false,
          error:error
        };
      }
    },
    async removeDocumentFromProfile(id)
    {
        try {
            const response = await client().get(
                'index.php?option=com_emundus&controller=form&task=removeDocumentFromProfile',
                {
                    params: {
                        did: id
                    }
                }
             );

            return response;
        } catch (error) {
            return {
                status: false,
                error:error
            };
        }
    }
};