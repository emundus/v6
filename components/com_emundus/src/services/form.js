import client from './axiosClient';
const baseUrl = 'index.php?option=com_emundus&controller=form';

export default {
    async updateFormLabel(params) {
        const formData = new FormData();
        Object.keys(params).forEach(key => formData.append(key, params[key]));

        try {
            const response = await client().post( baseUrl + '&task=updateformlabel', formData);

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
            const response = await client().get(baseUrl + '&task=getfilesbyform&pid=' + id);

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
                baseUrl + '&task=getsubmittionpage',
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
                baseUrl + '&task=getFormsByProfileId',
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
                error: error
            };
        }
    },
    async getFormByFabrikId(id) {
        try {
            const response = await client().get(baseUrl + '&task=getFormByFabrikId', {params: {form_id: id}});
            return response;
        } catch (error) {
            return {
                status: false,
                error: error
            };
        }
    },
    async getProfileLabelByProfileId(id)
    {
        const formData = new FormData();
        formData.append('profile_id', id);

        try {
            const response = await client().post(baseUrl + '&task=getProfileLabelByProfileId', formData);

            return response;
        } catch (error) {
            return {
                status: false,
                error: error
            };
        }
    },
    async getDocuments(id)
    {
        if (id > 0) {
            try {
                return await client().get(baseUrl + '&task=getDocuments', {params: {pid: id}});
            } catch (error) {
                return {
                    status: false,
                    error: error
                };
            }
        } else {
            return {
                status: false,
                error: 'Missing parameter'
            };
        }
    },
    async getDocumentModels(documentId = null) {
        try {
            let data = {
                status: false,
            };

            const response = await client().get(
                baseUrl + '&task=getAttachments'
            );

            if (response.data.status) {
                if (documentId !== null) {
                    const document = response.data.data.filter(document => document.id === documentId);
                    if (document.length > 0) {
                        data = {
                            status: true,
                            data: document[0]
                        };
                    } else {
                        data = {
                            status: false,
                            error: 'Document not found'
                        };
                    }
                } else {
                    data = response.data;
                }
            }

            return data;
        } catch (error) {
            return {
                status: false,
                error: error
            };
        }
    },
    async getDocumentModelsUsage(documentIds) {
        const formData = new FormData();
        formData.append('documentIds', documentIds);

        try {
            const response = await client().post(baseUrl + '&task=getdocumentsusage', formData);

            return response.data;
        } catch (error) {
            return {
                status: false,
                error: error
            };
        }
    },
    async getPageGroups(formId) {
        if (typeof formId == 'number' && formId > 0) {
            try {
                const response = await client().get(baseUrl + '&task=getpagegroups&form_id=' + formId);

                return response.data;
            } catch (error) {
                return {
                    status: false,
                    error: error
                };
            }
        } else {
            return {
                status: false,
                msg: 'MISSING_PARAMS'
            };
        }
    },
    async reorderDocuments(documents)
    {
        try {
            const formData = new FormData();
            formData.append('documents', JSON.stringify(documents));

            const response = await client().post(
                baseUrl + '&task=reorderDocuments',
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
    async addDocument(params)
    {
        const formData = new FormData();
        Object.keys(params).forEach(key => formData.append(key, params[key]));

        try {
            const response = await client().post(baseUrl + '&task=addDocument', formData);

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
          baseUrl + '&task=getassociatedcampaign',
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
                baseUrl + '&task=removeDocumentFromProfile',
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
    },
    async getPageObject(formId)
    {
        try {
            const response = await client().get(
                'index.php?option=com_emundus&view=form&formid=' + formId + '&format=vue_jsonclean'
            );
            return response;
        } catch (error) {
            return {
                status: false,
                error:error
            };
        }
    },
    async checkIfDocumentCanBeDeletedForProfile(documentId, profileId)
    {
        try {
            const response = await client().get(
                baseUrl + '&task=checkcandocbedeleted&docid=' + documentId + '&prid=' + profileId
            );

            return response.data;
        } catch (error) {
            return {
                status: false,
                error:error
            };
        }
    }
};
