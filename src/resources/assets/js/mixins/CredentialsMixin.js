import axios from 'axios';

export default {
    props: ['Settings'],
    data() {
        return {
            settings: {
                automatic_dispatch_enabled: false,
                dispatch_wait_time_limit: 10,
                dispatch_max_delivery: 3,
                ifood_client_id: null,
                ifood_client_secret: null,
            },
        };
    },
    methods: {
        saveSettings() {
            axios
                .post('/settings/application/save', {
                    settings: this.settings,
                })
                .then((response) => {
                    console.log(response.data);
                    if (response.data.success) {
                        this.reloadPageWithMessage(
                            this.trans('settings.config_update_alert')
                        );
                    } else {
                        this.showErrorMsg(response.data.errors);
                    }
                })
                .catch((error) => {
                    this.showErrorMsg(error);
                });
        },
        reloadPageWithMessage(message) {
            this.$swal({
                title: message,
            }).then((result) => {
                /* location.reload(); */
            });
        },
        showErrorMsg(errors) {
            this.$swal({
                title: this.trans('setting.error'),
                html:
                    '<label class="alert alert-danger alert-dismissable text-left">' +
                    errors +
                    '</label>',
                type: 'error',
            }).then((result) => {});
        },
        onChangeHubsterEnvironment() {
            this.settings.hubster_environment = 'production';
            if (!this.settings.hubster_environment_enabled)
                this.settings.hubster_environment = 'sandbox';
        },
    },
    created() {
        if (this.Settings) {
            this.settings = JSON.parse(this.Settings);
            console.log('has settings', this.settings);
        } else {
            axios
                .post('/settings/application/pairs', {
                    keys: Object.keys(this.settings).join(','),
                })
                .then((response) => {
                    console.log('pegou do criado', response.data);
                    this.settings = response.data;
                })
                .catch((error) => {
                    this.showErrorMsg(error);
                });
        }
    },
};
