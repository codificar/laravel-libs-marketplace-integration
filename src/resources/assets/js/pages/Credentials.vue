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
                    <div class="pull-right">
                        <toggle-button
                            :width="90"
                            v-model="settings.hubster_environment_enabled"
                            :sync="true"
                            :value="settings.hubster_environment_enabled"
                            @change="onChangeHubsterEnvironment"
                            :labels="{
                                checked: 'production',
                                unchecked: 'sandbox',
                            }"
                        />
                    </div>
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
                                type="button"
                                variant="success"
                                class="btn btn-success btn-flat btn-block"
                                @click="saveSettings"
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
import CredentialsMixin from '../mixins/CredentialsMixin';
export default {
    mixins: [CredentialsMixin],
};
</script>
<style>
button {
    color: white;
}
</style>
