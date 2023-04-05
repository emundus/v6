/**
 * Created by rob on 22/03/2016.
 */

define(['jquery'], function (jQuery) {
    var ListForm = new Class({

        autoChangeDbName: true,

        Implements: [Options],

        initialize: function (options) {
            var rows;
            window.addEvent('domready', function () {
                this.setOptions(options);
                this.watchTableDd();
                this.watchLabel();
                if (document.id('addAJoin')) {
                    document.id('addAJoin').addEvent('click', function (e) {
                        e.stop();
                        this.addJoin();
                    }.bind(this));
                }
                if (document.getElement('table.linkedLists')) {
                    rows = document.getElement('table.linkedLists').getElement('tbody');
                    new Sortables(rows, {
                        'handle': '.handle',
                        'onSort': function (element, clone) {
                            var s = this.serialize(1, function (item) {
                                if (item.getElement('input')) {
                                    return item.getElement('input').name.split('][').getLast().replace(']', '');
                                }
                                return '';
                            });
                            var actual = [];
                            s.each(function (i) {
                                if (i !== '') {
                                    actual.push(i);
                                }
                            });
                            document.getElement('input[name*=faceted_list_order]').value = JSON.stringify(actual);
                        }
                    });
                }

                if (document.getElement('table.linkedForms')) {
                    rows = document.getElement('table.linkedForms').getElement('tbody');
                    new Sortables(rows, {
                        'handle': '.handle',
                        'onSort': function (element, clone) {
                            var s = this.serialize(1, function (item) {
                                if (item.getElement('input')) {
                                    return item.getElement('input').name.split('][').getLast().replace(']', '');
                                }
                                return '';
                            });
                            var actual = [];
                            s.each(function (i) {
                                if (i !== '') {
                                    actual.push(i);
                                }
                            });
                            document.getElement('input[name*=faceted_form_order]').value = JSON.stringify(actual);
                        }
                    });
                }

                this.joinCounter = 0;
                this.watchOrderButtons();
                this.watchDbName();
                this.watchJoins();
            }.bind(this));

        },

        /**
         * Automatically fill in the db table name from the label if no
         * db table name existed when the form loaded and when the user has not
         * edited the db table name.
         */
        watchLabel: function () {
            this.autoChangeDbName = jQuery('#jform__database_name').val() === '';
            jQuery('#jform_label').on('keyup', function (e) {
                if (this.autoChangeDbName) {
                    var label = jQuery('#jform_label').val().trim().toLowerCase();
                    label = label.replace(/\W+/g, '_');
                    jQuery('#jform__database_name').val(label);
                }
            }.bind(this));

            jQuery('#jform__database_name').on('keyup', function () {
                this.autoChangeDbName = false;
            }.bind(this));
        },

        watchOrderButtons: function () {
            document.getElements('.addOrder').removeEvents('click');
            document.getElements('.deleteOrder').removeEvents('click');
            document.getElements('.addOrder').addEvent('click', function (e) {
                e.stop();
                this.addOrderBy();
            }.bind(this));
            document.getElements('.deleteOrder').addEvent('click', function (e) {
                e.stop();
                this.deleteOrderBy(e);
            }.bind(this));
        },

        addOrderBy: function (e) {
            var t;
            if (e) {
                t = e.target.getParent('.orderby_container');
            } else {
                t = document.getElement('.orderby_container');
            }
            t.clone().inject(t, 'after');
            this.watchOrderButtons();
        },

        deleteOrderBy: function (e) {
            if (document.getElements('.orderby_container').length > 1) {
                e.target.getParent('.orderby_container').dispose();
                this.watchOrderButtons();
            }
        },

        watchDbName: function () {
            if (document.id('database_name')) {
                document.id('database_name').addEvent('blur', function (e) {
                    if (document.id('database_name').get('value') === '') {
                        document.id('tablename').disabled = false;
                    } else {
                        document.id('tablename').disabled = true;
                    }
                });
            }
        },

        _buildOptions: function (data, sel) {
            var opts = [];
            if (data.length > 0) {
                if (typeof(data[0]) === 'object') {
                    data.each(function (o) {
                        if (o[0] === sel) {
                            opts.push(new Element('option', {'value': o[0], 'selected': 'selected'}).set('text', o[1]));
                        } else {
                            opts.push(new Element('option', {'value': o[0]}).set('text', o[1]));
                        }
                    });
                } else {
                    data.each(function (o) {
                        if (o === sel) {
                            opts.push(new Element('option', {'value': o, 'selected': 'selected'}).set('text', o));
                        } else {
                            opts.push(new Element('option', {'value': o}).set('text', o));
                        }
                    });
                }
            }
            return opts;
        },

        watchTableDd: function () {
            if (document.id('tablename')) {
                document.id('tablename').addEvent('change', function (e) {
                    var cid = document.getElement('input[name*=connection_id]').get('value');
                    var table = document.id('tablename').get('value');
                    var url = 'index.php?option=com_fabrik&format=raw&task=list.ajax_updateColumDropDowns&cid=' +
                        cid + '&table=' + table;
                    var myAjax = new Request({
                        url       : url,
                        method    : 'post',
                        onComplete: function (r) {
                            eval(r);
                        }
                    }).send();
                });
            }
        },

        watchFieldList: function (name) {
            document.getElement('div[id^=table-sliders-data]').addEvent('change:relay(select[name*=' + name + '])',
                function (e, target) {
                    this.updateJoinStatement(target.getParent('tr').id.replace('join', ''));
                }.bind(this));
        },

        _findActiveTables: function () {
            var t = document.getElements('.join_from').combine(document.getElements('.join_to'));
            t.each(function (sel) {
                var v = sel.get('value');
                if (this.options.activetableOpts.indexOf(v) === -1) {
                    this.options.activetableOpts.push(v);
                }
            }.bind(this));
            this.options.activetableOpts.sort();
        },

        addJoin: function (groupId, joinId, joinType, joinToTable, thisKey, joinKey, joinFromTable,
                           joinFromFields, joinToFields, repeat) {
            var repeaton, repeatoff, headings, row;
            joinType = joinType ? joinType : 'left';
            joinFromTable = joinFromTable ? joinFromTable : '';
            joinToTable = joinToTable ? joinToTable : '';
            thisKey = thisKey ? thisKey : '';
            joinKey = joinKey ? joinKey : '';
            groupId = groupId ? groupId : '';
            joinId = joinId ? joinId : '';
            repeat = repeat ? repeat : false;
            if (repeat) {
                repeaton = 'checked="checked"';
                repeatoff = '';
            } else {
                repeatoff = 'checked="checked"';
                repeaton = '';
            }
            this._findActiveTables();
            joinFromFields = joinFromFields ? joinFromFields : [['-', '']];
            joinToFields = joinToFields ? joinToFields : [['-', '']];

            var tbody = new Element('tbody');

            var ii = new Element('input', {
                'readonly': 'readonly',
                'size'    : '2',
                'class'   : 'disabled readonly input-mini',
                'name'    : 'jform[params][join_id][]',
                'value'   : joinId
            });

            var delButton = new Element('a', {
                'href'  : '#',
                'class' : 'btn btn-danger btn-sm',
                'events': {
                    'click': function (e) {
                        this.deleteJoin(e);
                        return false;
                    }.bind(this)
                }
            });

            var delHtml = '<i class="icon-minus"></i> ';
            delButton.set('html', delHtml);

            joinType = new Element('select', {
                'name' : 'jform[params][join_type][]',
                'class': 'form-select-sm inputbox'
            }).adopt(this._buildOptions(this.options.joinOpts, joinType));

            var joinFrom = new Element('select', {
                'name' : 'jform[params][join_from_table][]',
                'class': 'form-select-sm join_from inputbox'
            }).adopt(this._buildOptions(this.options.activetableOpts, joinFromTable));

            groupId = new Element('input', {'type': 'hidden', 'name': 'group_id[]', 'value': groupId});

            var tableJoin = new Element('select', {
                'name' : 'jform[params][table_join][]',
                'class': 'form-select-sm join_to inputbox'
            }).adopt(this._buildOptions(this.options.tableOpts, joinToTable));

            var tableKey = new Element('select', {
                'name' : 'jform[params][table_key][]',
                'class': 'table_key inputbox form-select-sm'
            }).adopt(this._buildOptions(joinFromFields, thisKey));

            joinKey = new Element('select', {
                'name' : 'jform[params][table_join_key][]',
                'class': 'table_join_key inputbox form-select-sm'
            }).adopt(this._buildOptions(joinToFields, joinKey));

            var repeatRadio =
                "<fieldset class=\"radio\">" +
 
                "<input type=\"radio\" id=\"joinrepeatno" + this.joinCounter +
                "\" value=\"0\" name=\"jform[params][join_repeat][" + this.joinCounter +
                "][]\" " + repeatoff + "/><label style=\"padding: 0.1rem 0.6rem 0.1rem 0.2rem\" for=\"joinrepeatno" + this.joinCounter +
                "\">" + Joomla.JText._('JNO') + "</label>" +

				"<input type=\"radio\" id=\"joinrepeat" + this.joinCounter +
                "\" value=\"1\" name=\"jform[params][join_repeat][" + this.joinCounter + "][]\" " +
                repeaton + "/><label style=\"padding: 0.1rem 0.6rem 0.1rem 0.2rem\" for=\"joinrepeat" + this.joinCounter + "\">" + Joomla.JText._('JYES') +
                "</label>" +
 
				"</fieldset>";

            headings = new Element('thead').adopt(
                new Element('tr').adopt([
                    new Element('th').set('text', 'id'),
                    new Element('th').set('text', Joomla.JText._('COM_FABRIK_JOIN_TYPE')),
                    new Element('th').set('text', Joomla.JText._('COM_FABRIK_FROM')),
                    new Element('th').set('text', Joomla.JText._('COM_FABRIK_TO')),
                    new Element('th').set('text', Joomla.JText._('COM_FABRIK_FROM_COLUMN')),
                    new Element('th').set('text', Joomla.JText._('COM_FABRIK_TO_COLUMN')),
                    new Element('th').set('text', Joomla.JText._('COM_FABRIK_REPEAT_GROUP_BUTTON_LABEL')),
                    new Element('th')
                ])
            );

            row = new Element('tr', {'id': 'join' + this.joinCounter}).adopt([
                new Element('td').adopt(ii),
                new Element('td').adopt([groupId, joinType]),
                new Element('td').adopt(joinFrom),
                new Element('td').adopt(tableJoin),
                new Element('td.table_key').adopt(tableKey),
                new Element('td.table_join_key').adopt(joinKey),
                new Element('td').set('html', repeatRadio),
                new Element('td').adopt(delButton)
            ]);
            var id = '';
            var sContent = new Element('table', {'class': 'table-striped table', 'id': id}).adopt([
                headings,
                tbody.adopt(row)
            ]);
 
            if (this.joinCounter === 0) {
                sContent.inject(document.id('joindtd'));
            } else {
                var tb = document.id('joindtd').getElement('tbody');
                row.inject(tb);
            }

            this.joinCounter++;
        },

        deleteJoin: function (e) {
            var tbl, t;
            e.stop();
            t = e.target.getParent('tr');
            tbl = e.target.getParent('table');
            t.dispose();
            if (tbl.getElements('tbody tr').length === 0) {
                tbl.dispose();
            }
        },

        watchJoins: function () {
            document.getElement('div[id^=table-sliders-data]').addEvent('change:relay(.join_from)', function (e, target) {
                var row = target.getParent('tr');
                var activeJoinCounter = row.id.replace('join', '');
                this.updateJoinStatement(activeJoinCounter);
                var table = target.get('value');
                var conn = document.getElement('input[name*=connection_id]').get('value');

                var update = row.getElement('td.table_key');
                var url = 'index.php?option=com_fabrik&format=raw&task=list.ajax_loadTableDropDown&table=' + table + '&conn=' + conn;
                var myAjax = new Request.HTML({
                    url   : url,
                    method: 'post',
                    update: update
                }).send();
            }.bind(this));

            document.getElement('div[id^=table-sliders-data]').addEvent('change:relay(.join_to)', function (e, target) {
                var row = target.getParent('tr');
                var activeJoinCounter = row.id.replace('join', '');
                this.updateJoinStatement(activeJoinCounter);
                var table = target.get('value');
                var conn = document.getElement('input[name*=connection_id]').get('value');
                var url = 'index.php?name=jform[params][table_join_key][]&option=com_fabrik&format=raw&task=list.ajax_loadTableDropDown&table=' + table + '&conn=' + conn;

                var update = row.getElement('td.table_join_key');
                var myAjax = new Request.HTML({
                    url   : url,
                    method: 'post',
                    update: update
                }).send();
            }.bind(this));
            this.watchFieldList('join_type');
            this.watchFieldList('table_join_key');
            this.watchFieldList('table_key');
        },

        updateJoinStatement: function (activeJoinCounter) {
            var fields = document.getElements('#join' + activeJoinCounter + ' .inputbox');
            fields = Array.mfrom(fields);
            var type = fields[0].get('value');
            var fromTable = fields[1].get('value');
            var toTable = fields[2].get('value');
            var fromKey = fields[3].get('value');
            var toKey = fields[4].get('value');
            var str = type + " JOIN " + toTable + " ON " + fromTable + "." + fromKey + " = " + toTable + "." + toKey;
            var desc = document.id('join-desc-' + activeJoinCounter);
            if (typeOf(desc) !== 'null') {
                desc.set('html', str);
            }

        }

    });

    return ListForm;
});