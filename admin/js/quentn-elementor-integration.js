(function ($) {
    var initialized = false;

    function initializeQuentn() {
        if (initialized) {
            return;
        }
        var e = window.elementor;
        var ep = window.elementorPro;

        // Wait until the dependencies are available.
        if (
            !e ||
            !e.modules ||
            !e.modules.controls ||
            !e.modules.controls.Fields_map ||
            !e.modules.controls.Base ||
            typeof e.addControlView !== 'function' ||
            !ep ||
            !ep.modules ||
            !ep.modules.forms ||
            !ep.modules.forms.getresponse ||
            typeof ep.modules.forms.getresponse.addSectionListener !== 'function' ||
            !window.QntnMappableFields ||
            !Array.isArray(window.QntnMappableFields.fields)
        ) {
            return;
        }

        // Prevent duplicate initialization.
        initialized = true;
        $(window).off('elementor:init.quentn', initializeQuentn);

        (function (e, ep, fields, $) {
            var QuentnIntegration = {
                fields: fields,
                customFields: {},

                getName: function getName() {
                    return 'quentn';
                },

                onElementChange: function onElementChange(setting) {
                    switch (setting) {
                        case 'quentn_api_credentials_source':
                        case 'quentn_api_key':
                        case 'quentn_api_url':
                            this.onApiUpdate();
                            break;
                    }
                },

                fetchCache: function fetchCache(type, cacheKey, requestArgs) {
                    var _this = this;

                    return elementorPro.ajax.addRequest('forms_panel_action_data', {
                        unique_id: 'integrations_' + this.getName(),
                        data: requestArgs,

                        success: function success(data) {
                            _this.cache[type] = _.extend({}, _this.cache[type]);
                            _this.cache[type][cacheKey] = data[type];
                        },

                        error: function (request, status, error) {
                            _this.updateOptions('quentn_list', []);
                            _this.customFields = {};
                        }
                    });
                },

                onApiUpdate: function onApiUpdate() {
                    var self = this,
                        apikeyControlView = self.getEditorControlView('quentn_api_key'),
                        apiUrlControlView = self.getEditorControlView('quentn_api_url'),
                        apiCredControlView = self.getEditorControlView('quentn_api_credentials_source');

                    if (
                        'default' !== apiCredControlView.getControlValue() &&
                        (
                            '' === apikeyControlView.getControlValue() ||
                            '' === apiUrlControlView.getControlValue()
                        )
                    ) {
                        self.updateOptions('quentn_list', []);
                        self.getEditorControlView('quentn_list').setValue('');
                        return;
                    }

                    self.addControlSpinner('quentn_list');
                    self.addControlSpinner('quentn_fields_map');

                    var cacheKey = this.getCacheKey({
                        controls: [
                            apiCredControlView.getControlValue(),
                            apiUrlControlView.getControlValue(),
                            apikeyControlView.getControlValue()
                        ]
                    });

                    self.getQuentnCache('terms', 'quentn_list', cacheKey)
                        .done(function (data) {
                            self.updateOptions('quentn_list', data.terms);
                        })
                        .always(function () {
                            self.getQuentnCache('fields', 'quentn_list', cacheKey)
                                .done(function (data) {
                                    self.customFields = data.fields;
                                })
                                .always(function () {
                                    self.updateFieldsMap();
                                });
                        });
                },

                onSectionActive: function onSectionActive() {
                    this.updateFieldsMap();
                    this.onApiUpdate();
                },

                updateFieldsMap: function updateFieldsMap() {
                    var fieldsList = fields;

                    if (!jQuery.isEmptyObject(this.customFields)) {
                        fieldsList = fields.concat(this.customFields);
                    }

                    this.getEditorControlView('quentn_fields_map')
                        .updateMap(fieldsList);
                },

                getQuentnCache: function getQuentnCache(type, action, cacheKey, requestArgs) {
                    if (_.has(this.cache[type], cacheKey)) {
                        var data = {};
                        data[type] = this.cache[type][cacheKey];

                        return jQuery.Deferred().resolve(data);
                    }

                    requestArgs = _.extend({}, requestArgs, {
                        service: 'quentn',
                        quentn_action: action,
                        api_key: this.getEditorControlView('quentn_api_key').getControlValue(),
                        api_url: this.getEditorControlView('quentn_api_url').getControlValue(),
                        api_cred: this.getEditorControlView('quentn_api_credentials_source').getControlValue()
                    });

                    return this.fetchCache(type, cacheKey, requestArgs);
                }
            };

            /**
             * Quentn field mapping control.
             */
            var QntnFieldsMap = e.modules.controls.Fields_map.extend({
                onBeforeRender: function onBeforeRender() {
                    this.$el.hide();
                },

                updateMap: function updateMap(fields) {
                    var self = this,
                        savedMapObject = {};

                    self.collection.each(function (model) {
                        savedMapObject[model.get('local_id')] = model.get('remote_id');
                    });

                    self.collection.reset();

                    var formFields = self.elementSettingsModel.get('form_fields').models;

                    _.each(formFields, function (field) {
                        var model = {
                            local_id: field.get('custom_id'),
                            local_label: field.get('field_label'),
                            local_type: field.get('field_type'),
                            allow_multiple: (field.get('allow_multiple') === 'true'),
                            remote_id: savedMapObject[field.get('custom_id')]
                                ? savedMapObject[field.get('custom_id')]
                                : ''
                        };

                        self.collection.add(model);
                    });

                    self.render();
                },

                getFieldOptions: function getFieldOptions() {
                    if (jQuery.isEmptyObject(ep.modules.forms.quentn.customFields)) {
                        return ep.modules.forms.quentn.fields;
                    }

                    return ep.modules.forms.quentn.fields.concat(
                        ep.modules.forms.quentn.customFields
                    );
                },

                onRender: function onRender() {
                    e.modules.controls.Base.prototype.onRender.apply(this, arguments);

                    var self = this;

                    self.children.each(function (view) {
                        var localFieldsControl = view.children.last(),
                            options = {
                                '': '- ' + elementor.translate('None') + ' -'
                            },
                            label = view.model.get('local_label');

                        _.each(self.getFieldOptions(), function (model, index) {
                            var localType = view.model.get('local_type');
                            var remoteType = model.remote_type;

                            if (localType === 'radio') {
                                // Map radio fields like single-select fields.
                                localType = 'select';
                            } else if (
                                localType === 'url' ||
                                localType === 'textarea' ||
                                localType === 'hidden'
                            ) {
                                localType = 'text';
                            } else if (
                                localType === 'select' &&
                                view.model.get('allow_multiple') === true
                            ) {
                                // Map multi-select fields to Quentn multiple-choice fields.
                                localType = 'checkbox';
                            } else if (
                                localType === 'time' &&
                                model.remote_id !== 'date_of_birth'
                            ) {
                                // Time fields can map to datetime fields.
                                localType = 'date';
                            }

                            if (localType !== remoteType) {
                                return;
                            }

                            if (remoteType === 'date') {
                                // Preserve whether the local field supplies a date or time.
                                options[
                                    [model.remote_id, view.model.get('local_type')]
                                    ] = model.remote_label || 'Field #' + (index + 1);
                            } else {
                                options[model.remote_id] =
                                    model.remote_label || 'Field #' + (index + 1);
                            }
                        });

                        localFieldsControl.model.set('label', label);
                        localFieldsControl.model.set('options', options);

                        localFieldsControl.render();

                        view.$el.find('.elementor-repeater-row-tools').hide();

                        view.$el.find('.elementor-repeater-row-controls')
                            .removeClass('elementor-repeater-row-controls')
                            .find('.elementor-control')
                            .css({
                                padding: '10px 0'
                            });
                    });

                    self.$el.find('.elementor-button-wrapper').remove();

                    if (self.children.length) {
                        self.$el.show();
                    }
                }
            });

            // Register the control view.
            e.addControlView('Qntn_fields_map', QntnFieldsMap);

            QuentnIntegration = Object.assign(
                ep.modules.forms.getresponse,
                QuentnIntegration
            );

            ep.modules.forms.quentn = QuentnIntegration;

            ep.modules.forms.quentn.addSectionListener(
                'section_quentn_elementor',
                QuentnIntegration.onSectionActive
            );
        })(e, ep, window.QntnMappableFields.fields, $);
    }

    // Listen in case Elementor has not initialized yet.
    $(window).on('elementor:init.quentn', initializeQuentn);

    // Also try immediately in case the event has already fired.
    initializeQuentn();
})(jQuery);