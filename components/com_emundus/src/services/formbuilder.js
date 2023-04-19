import client from './axiosClient';

export default {
    async createSimpleElement(params) {
        try {
            const formData = new FormData();
            Object.keys(params).forEach(key => {
                formData.append(key, params[key]);
            });

            const response = await client().post(
                'index.php?option=com_emundus&controller=formbuilder&task=createsimpleelement',
                formData
            );

            return response;
        } catch (e) {
            return {
                status: false,
                message: e.message
            };
        }
    },
    async createSectionSimpleElements(params)
    {
        try {
            const formData = new FormData();
            Object.keys(params).forEach(key => {
                formData.append(key, params[key]);
            });

            const response = await client().post(
                'index.php?option=com_emundus&controller=formbuilder&task=createsectionsimpleelements',
                formData
            );

            return response;
        } catch (e) {
            return {
                status: false,
                message: e.message
            };
        }
    },
    async createSimpleGroup(fid, label)
    {
        try {
            const formData = new FormData();
            formData.append('fid', fid);
            formData.append('label', JSON.stringify(label));

            return await client().post(
                'index.php?option=com_emundus&controller=formbuilder&task=createsimplegroup',
                formData
            );
        } catch (e) {
            return {
                status: false,
                message: e.message
            };
        }
    },
    async createTestingFile(campaign_id)
    {
        try {
            const formData = new FormData();
            formData.append('cid', campaign_id);

            const response = await client().post(
                'index.php?option=com_emundus&controller=formbuilder&task=createtestingfile',
                formData
            );

            return response;
        } catch (e) {
            return {
                status: false,
                message: e.message
            };
        }
    },
    async getElement(gid, element)
    {
        try {
            const response = await client().get(
                'index.php?option=com_emundus&controller=formbuilder&task=getElement',
                {
                    params: {
                        gid: gid,
                        element: element
                    }
                }
            );

            return response;
        } catch (e) {
            return {
                status: false,
                message: e.message
            };
        }
    },
    async getJTEXT(text)
    {
        const formData = new FormData();
        formData.append('toJTEXT', text);

        try {
            const response = client().post('index.php?option=com_emundus&controller=formbuilder&task=getJTEXT',
                formData
            );

            return response;
        }  catch (e) {
            return {
                status: false,
                message: e.message
            };
        }
    },
    async getJTEXTA(texts) {
        const formData = new FormData();
        texts.forEach((text, index) => {
            formData.append('toJTEXT['+ index +']', text);
        });

        try {
            const response = client().post('index.php?option=com_emundus&controller=formbuilder&task=getJTEXTA',
                formData
            );

            return response;
        }  catch (e) {
            return {
                status: false,
                message: e.message
            };
        }
    },
    async getAllTranslations(text)
    {
        const formData = new FormData();
        formData.append('toJTEXT', text);

        try {
            const response = client().post(
                'index.php?option=com_emundus&controller=formbuilder&task=getalltranslations',
                formData
            );

            return response;
        }  catch (e) {
            return {
                status: false,
                message: e.message
            };
        }
    },
    async getTestingParams(id)
    {
        try {
            const response = await client().get(
                'index.php?option=com_emundus&controller=formbuilder&task=gettestingparams',
                {
                    params: {
                        prid: id
                    }
                }
            );

            return response;
        } catch (e) {
            return {
                status: false,
                message: e.message
            };
        }
    },
    async getDatabases()
    {
        try {
            const response = await client().get(
                'index.php?option=com_emundus&controller=formbuilder&task=getdatabasesjoin'
            );

            return response;
        } catch (e) {
            return {
                status: false,
                message: e.message
            };
        }
    },
    getDatabaseJoinOrderColumns(databaseName) {
        try {
            const response = client().get(
                'index.php?option=com_emundus&controller=formbuilder&task=getDatabaseJoinOrderColumns',
                {
                    params: {
                        database_name: databaseName
                    }
                }
            );

            return response;
        } catch (e) {
            return {
                status: false,
                message: e.message
            };
        }
    },
    async updateParams(element)
    {
        const formData = new FormData();
        const postData = JSON.stringify(element);
        formData.append('element', postData);

        try {
            const response = await client().post(
                'index.php?option=com_emundus&controller=formbuilder&task=updateparams',
                formData
            );

            return response;
        } catch (e) {
            return {
                status: false,
                message: e.message
            };
        }
    },
    async updateGroupParams(group_id, params, lang = null) {
        const formData = new FormData();
        formData.append('group_id', group_id);
        formData.append('params', JSON.stringify(params));
        if (lang != null) {
            formData.append('lang', lang);
        }

        try {
            const response = await client().post(
                'index.php?option=com_emundus&controller=formbuilder&task=updategroupparams',
                formData
            );

            return response;
        } catch (e) {
            return {
                status: false,
                message: e.message
            };
        }
    },
    async updateOrder(elements, groupId, movedElement) {
        const formData = new FormData();
        formData.append('elements', JSON.stringify(elements));
        formData.append('group_id', groupId);
        formData.append('moved_el', JSON.stringify(movedElement));

        if (movedElement.length == 0) {
            return {
                status: false,
                message: 'No elements to update'
            };
        }

        try {
            const response = await client().post(
                'index.php?option=com_emundus&controller=formbuilder&task=updateOrder',
                formData
            );

            return response;
        } catch (e) {
            return {
                status: false,
                message: e.message
            };
        }
    },
    updateElementOrder(groupId, elementId, newIndex) {
      const formData = new FormData();
      formData.append('group_id', groupId);
      formData.append('element_id', elementId);
      formData.append('new_index', newIndex);

      try {
        const response = client().post(
          'index.php?option=com_emundus&controller=formbuilder&task=updateelementorder',
          formData
        );

        return response;
      } catch (e) {
        return {
          status: false,
          message: e.message
        };
      }
    },
    async updateDocument(data) {
        const formData = new FormData();
        formData.append('document_id', data.document_id);
        formData.append('profile_id', data.profile_id);
        formData.append('document', data.document);
        formData.append('types', data.types);

        try {
            const response = await client().post(
                'index.php?option=com_emundus&controller=formbuilder&task=updatedocument',
                formData
            );

            return response;
        } catch (e) {
            return {
                status: false,
                msg: e
            };
        }
    },

    async toggleElementPublishValue(element)
    {
        const formData = new FormData();
        formData.append('element', element);

        try {
            const response = await client().post(
                'index.php?option=com_emundus&controller=formbuilder&task=publishunpublishelement',
                formData
            );

            return response;
        } catch (e) {
            return {
                status: false,
                message: e.message
            };
        }
    },
    async toggleElementHiddenValue(element){
        const formData = new FormData();
        formData.append('element', element);

        try {
            const response = await client().post(
                'index.php?option=com_emundus&controller=formbuilder&task=hiddenunhiddenelement',
                formData
            );

            return response;
        } catch (e) {
            return {
                status: false,
                message: e.message
            };
        }
    },
    async reorderMenu(params,profile_id)
    {
        const formData = new FormData();
        formData.append('menus', JSON.stringify(params));
        formData.append('profile', profile_id);

        try {
            return await client().post(
                'index.php?option=com_emundus&controller=formbuilder&task=reordermenu',
                formData
            );
        } catch (e) {
            return {
                status: false,
                message: e.message
            };
        }
    },

    async reorderSections(pageId, sections)
    {
        const formData = new FormData();
        formData.append('groups', JSON.stringify(sections));
        formData.append('fid', pageId);

        try {
            const response = await client().post(
                'index.php?option=com_emundus&controller=formbuilder&task=reordergroups',
                formData
            );

            return response;
        } catch (e) {
            return {
                status: false,
                message: e.message
            };
        }
    },

    async addPage(params) {
        if (!params.prid) {
            return {
                status: false,
                msg: 'Missing prid'
            };
        }

        const formData = new FormData();
        Object.keys(params).forEach(key => {
            formData.append(key, JSON.stringify(params[key]));
        });

        try {
            return await client().post(
                'index.php?option=com_emundus&controller=formbuilder&task=createmenu',
                formData
            );
        } catch (e) {
            return {
                status: false,
                msg: e.message
            };
        }
    },
    async deletePage(page){
        if (!page) {
            return {
                status: false,
                message: 'Missing page id'
            };
        }

        const formData = new FormData();
        formData.append('mid', page);

        try {
            return await client().post(
                'index.php?option=com_emundus&controller=formbuilder&task=deletemenu',
                formData
            );
        } catch (e) {
            return {
                status: false,
                message: e.message
            };
        }
    },
    async updateTranslation(item, tag, value) {
        const formData = new FormData();

        if (item !== null) {
            formData.append(item.key, item.value);
        }
        formData.append('labelTofind', tag);
        Object.keys(value).forEach(key => {
            formData.append('NewSubLabel[' + key + ']', value[key]);
        });

        try {
            const response = await client().post(
                'index.php?option=com_emundus&controller=formbuilder&task=formsTrad',
                formData
            );

            return response;
        } catch (e) {
            return {
                status: false,
                message: e.message
            };
        }
    },

    async updateOption(elementId, options, index, newTranslation, lang) {
        const formData = new FormData();
        formData.append('element', elementId);
        formData.append('options', JSON.stringify(options));
        formData.append('index', index);
        formData.append('newTranslation', newTranslation);
        formData.append('lang', lang);

        try {
            const response = client().post(
                'index.php?option=com_emundus&controller=formbuilder&task=updateElementOption',
                formData
            );

            return response;
        } catch (e) {
            return {
                status: false,
                message: e.message
            };
        }
    },
    addOption(element, newOption, lang) {
        const formData = new FormData();
        formData.append('element', element);
        formData.append('newOption', newOption);
        formData.append('lang', lang);

        try {
            const response = client().post(
                'index.php?option=com_emundus&controller=formbuilder&task=addElementSubOption',
                formData
            );

            return response;
        } catch (e) {
            return {
                status: false,
                message: e.message
            };
        }
    },
    deleteElementSubOption(element, index) {
        const formData = new FormData();
        formData.append('element', element);
        formData.append('index', index);

        try {
            const response = client().post(
                'index.php?option=com_emundus&controller=formbuilder&task=deleteElementSubOption',
                formData
            );

            return response;
        } catch (e) {
            return {
                status: false,
                message: e.message
            };
        }
    },
    updateElementSubOptionsOrder(element, old_order, new_order)
    {
        const formData = new FormData();
        formData.append('element', element);
        formData.append('options_old_order', JSON.stringify(old_order));
        formData.append('options_new_order', JSON.stringify(new_order));

        try {
            const response =  client().post(
              'index.php?option=com_emundus&controller=formbuilder&task=updateElementSubOptionsOrder' ,
              formData
            );

            return response;
        } catch (e) {
            return {
                status: false,
                message: e.message
            };
        }
    },
    getElementSubOptions(element) {
        try {
            const response = client().get(
                'index.php?option=com_emundus&controller=formbuilder&task=getelementsuboptions',
                {
                    params: {
                        element: element
                    }
                }
            );

            return response;
        } catch (e) {
            return {
                status: false,
                message: e.message
            };
        }
    },

    deleteElement(elementId) {
        const formData = new FormData();
        formData.append('element', elementId);

        try {
            const response = client().post(
                'index.php?option=com_emundus&controller=formbuilder&task=deleteElement',
                formData
            );

            return response;
        } catch (e) {
            return {
                status: false,
                message: e.message
            };
        }
    },
    deleteGroup(groupId) {
        const formData = new FormData();
        formData.append('gid', groupId);

        try {
            const response = client().post(
                'index.php?option=com_emundus&controller=formbuilder&task=deleteGroup',
                formData
            );

            return response;
        } catch (e) {
            return {
                status: false,
                message: e.message
            };
        }
    },
    async updateDefaultValue(eid,value) {
        const formData = new FormData();
        formData.append('eid', eid);
        formData.append('value', value);

        try {
            return client().post(
                'index.php?option=com_emundus&controller=formbuilder&task=updatedefaultvalue',
                formData
            );
        } catch (e) {
            return {
                status: false,
                message: e.message
            };
        }
    },
    async getAllDatabases()
    {
        try {
            const response = await client().get(
                'index.php?option=com_emundus&controller=formbuilder&task=getalldatabases'
            );

            return response;
        } catch (e) {
            return {
                status: false,
                message: e.message
            };
        }
    },
    async getSection(section)
    {
        try {
            const response = await client().get(
                'index.php?option=com_emundus&controller=formbuilder&task=getsection',
                {
                    params: {
                        section: section
                    }
                }
            );

            return response;
        } catch (e) {
            return {
                status: false,
                message: e.message
            };
        }
    },
    async getModels()
    {
        try {
            const response = await client().get(
                'index.php?option=com_emundus&controller=formbuilder&task=getpagemodels'
            );

            return response.data;
        } catch (e) {
            return {status: false, message: e.message};
        }
    },
    async addFormModel(formId, modelLabel) {
        if (formId > 0) {
            const formData = new FormData();
            formData.append('form_id', formId);
            formData.append('label', modelLabel);

            try {
                const response = await client().post(
                    'index.php?option=com_emundus&controller=formbuilder&task=addformmodel', formData
                );

                return response.data;
            } catch (e) {
                return {status: false, message: e.message};
            }
        } else {
            return {status: false, message: 'MISSING_PARAMS'};
        }
    },
    async deleteFormModel(formId) {
        if (formId > 0) {
            const formData = new FormData();
            formData.append('form_id', formId);

            try {
                const response = await client().post(
                    'index.php?option=com_emundus&controller=formbuilder&task=deleteformmodel', formData
                );

                return response.data;
            } catch (e) {
                return {status: false, message: e.message};
            }
        } else {
            return {status: false, message: 'MISSING_PARAMS'};
        }
    },
    async deleteFormModelFromId(modelIds) {
        if (modelIds.length > 0) {
            const formData = new FormData();
            formData.append('model_ids', JSON.stringify(modelIds));

            try {
                const response = await client().post(
                    'index.php?option=com_emundus&controller=formbuilder&task=deleteformmodelfromids', formData
                );

                return response.data;
            } catch (e) {
                return {status: false, message: e.message};
            }
        } else {
            return {status: false, message: 'MISSING_PARAMS'};
        }
    },
    async getDocumentSample(documentId, profileId) {
        if (documentId > 0 && profileId > 0) {
            try {
                const response = await client().get(
                    'index.php?option=com_emundus&controller=formbuilder&task=getdocumentsample',
                    {
                        params: {
                            document_id: documentId,
                            profile_id: profileId
                        }
                    }
                );

                return response.data;
            } catch (e) {
                return {status: false, message: e.message};
            }
        } else {
            return {status: false, message: 'MISSING_PARAMS'};
        }

    }
};
