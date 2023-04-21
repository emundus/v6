import client from "../services/axiosClient";

var fabrik = {
    methods: {
        async getDatabasejoinOptions(table_name, column_name, value, concat_value, where_clause) {
            try {
                const response = await client().get('index.php?option=com_emundus&controller=form&task=getdatabasejoinoptions', {
                    params: {
                        table_name: table_name,
                        column_name: column_name,
                        value: value,
                        concat_value: concat_value,
                        where_clause: where_clause,
                    }
                });

                return response.data;
            } catch (e) {
                return {
                    status: false,
                    msg: e.message
                };
            }
        }
    }
};

export default fabrik;
