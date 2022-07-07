<template>
    <div>
        <b-form @submit="saveSettings" @reset="settings = []">
            <div class="card card-outline-info">
                <div class="card-header">
                    <h4 class="text-white m-b-0">
                        {{ trans('settings.automatic_dispatch') }}
                    </h4>
                    <div class="pull-right">
                        <toggle-button
                            v-model="settings.automatic_dispatch_enabled"
                            :sync="true"
                            :value="settings.automatic_dispatch_enabled"
                        />
                    </div>
                </div>

                <div
                    class="card-block"
                    v-if="settings.automatic_dispatch_enabled"
                >
                    <div class="row">
                        <div class="col-md-6 col-sm-12">
                            <b-form-group
                                id="input-group-1"
                                :label="
                                    trans('settings.dispatch_wait_time_limit')
                                "
                                label-for="dispatch_wait_time_limit"
                            >
                                <b-form-input
                                    id="dispatch_wait_time_limit"
                                    v-model="settings.dispatch_wait_time_limit"
                                    type="number"
                                    required
                                ></b-form-input>
                            </b-form-group>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <b-form-group
                                id="input-group-2"
                                :label="trans('settings.dispatch_max_delivery')"
                                label-for="dispatch_max_delivery"
                            >
                                <b-form-input
                                    id="dispatch_max_delivery"
                                    v-model="settings.dispatch_max_delivery"
                                    type="number"
                                    required
                                ></b-form-input>
                            </b-form-group>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-outline-info">
                <div class="card-header">
                    <h4 class="text-white m-b-0">
                        {{ trans('settings.ifood_credentials') }}
                    </h4>
                </div>

                <div class="card-block">
                    <div class="row">
                        <div class="col-md-6 col-sm-12">
                            <b-form-group
                                id="input-group-1"
                                :label="trans('settings.ifood_client_id')"
                                label-for="ifood_client_id"
                            >
                                <b-form-input
                                    id="ifood_client_id"
                                    v-model="settings.ifood_client_id"
                                    type="text"
                                    required
                                ></b-form-input>
                            </b-form-group>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <b-form-group
                                id="input-group-2"
                                :label="trans('settings.ifood_client_secret')"
                                label-for="ifood_client_secret"
                            >
                                <b-form-input
                                    id="ifood_client_secret"
                                    v-model="settings.ifood_client_secret"
                                    type="text"
                                    required
                                ></b-form-input>
                            </b-form-group>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card card-outline-info">
                <div class="card-header">
                    <h4 class="text-white m-b-0">
                        {{ trans('settings.hubster_credentials') }}
                    </h4>
                </div>

                <div class="card-block">
                    <div class="row">
                        <div class="col-md-6 col-sm-12">
                            <b-form-group
                                id="input-group-1"
                                :label="trans('settings.hubster_client_id')"
                                label-for="hubster_client_id"
                            >
                                <b-form-input
                                    id="hubster_client_id"
                                    v-model="settings.hubster_client_id"
                                    type="text"
                                    required
                                ></b-form-input>
                            </b-form-group>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <b-form-group
                                id="input-group-2"
                                :label="trans('settings.hubster_client_secret')"
                                label-for="hubster_client_secret"
                            >
                                <b-form-input
                                    id="hubster_client_secret"
                                    v-model="settings.hubster_client_secret"
                                    type="text"
                                    required
                                ></b-form-input>
                            </b-form-group>
                        </div>
                    </div>
                </div>
            </div>

            <div class="box-footer">
                <div class="pull-right">
                    <div class="row">
                        <div class="col">
                            <b-button
                                type="reset"
                                class="btn btn-inverse btn-flat"
                                >{{ trans('settings.reset') }}</b-button
                            >
                        </div>
                        <div class="col">
                            <b-button
                                type="submit"
                                variant="success"
                                class="btn btn-success btn-flat btn-block"
                                >{{ trans('settings.save') }}</b-button
                            >
                        </div>
                    </div>
                </div>
            </div>
        </b-form>
    </div>
</template>

<script>
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
                hubster_client_id: null,
                hubster_client_secret: null,
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
    },
    created() {
        if (this.Settings) {
            this.settings = JSON.parse(this.Settings);
            console.log(this.settings);
        } else {
            axios
                .post('/settings/application/pairs', {
                    keys: Object.keys(this.settings).join(','),
                })
                .then((response) => {
                    console.log(response.data);
                    this.settings = response.data;
                })
                .catch((error) => {
                    this.showErrorMsg(error);
                });
        }
    },
};
</script>
<style>
button {
    color: white;
}
</style>
