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
            formData.append('label', label);

            const response = await client().post(
                'index.php?option=com_emundus&controller=formbuilder&task=createsimplegroup',
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
    async updateGroupParams(group_id, params) {
        const formData = new FormData();
        formData.append('group_id', group_id);
        formData.append('params', JSON.stringify(params));

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
    async reorderMenu(params)
    {
        const formData = new FormData();
        Object.keys(params).forEach(key => {
            formData.append(key, params[key]);
        });

        try {
            const response = await client().post(
                'index.php?option=com_emundus&controller=formbuilder&task=reordermenu',
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
                message: 'Missing prid'
            };
        }

        const formData = new FormData();
        Object.keys(params).forEach(key => {
            formData.append(key, params[key]);
        });

        try {
            const response = await client().post(
                'index.php?option=com_emundus&controller=formbuilder&task=createmenu',
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
    }
};