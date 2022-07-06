<template>
    <div class="tab-content">
        <div class="col-lg-12">
            <div class="card card-outline-info">
                <div class="card-header">
                    <h4 class="m-b-0 text-white">{{ trans('zedelivery.import_financial') }}</h4>
                </div>

                <div class="card-block">

                    <div class="row">
                        
                        <div class="col-md-6 col-sm-12">
                            <div class="form-group">
                                <label for="" class="control-label">{{ trans('zedelivery.select_file') }} * ( <a class="link_csv_model" target="_blank" href="https://drive.google.com/file/d/1KuYfdtMqItpfQZAXB24SDTzCiLTl-5PZ/view?usp=sharing">{{ trans('zedelivery.file_example') }}</a> )</label>
                                <input @change="onFileChange" type="file" class="form-control">
                            </div>
                        </div>

                    </div>

                    <div class="box-footer">					
                        <div class="pull-right">
                            <div class="row">
                                <div class="col">
                                    <button @click="submitImportFinancial" type="button" class="btn btn-success">
                                        {{ trans('zedelivery.upload') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    props: [
    ],

    data () {
        return {
            selectedFile: null,
            disabled: true
        }
    },

    methods: {

        /**
         * Requisição para enviar um csv para
         * realizar a associação em massa
         */
        submitImportFinancial () {
            if (this.selectedFile) {
                const formData = new FormData();
    
                formData.append('csv_file', this.selectedFile);
    
                axios.post('/admin/marketplace-integration/zedelivery/import-financial', formData)
                .then(res => {
                    if (res.data.success)
                        this.$swal({
                            type: 'success',
                            title: this.trans('zedelivery.import_financial_success')
                        });
                    else if (!res.data.success && res.data.errors.length > 0)
                        this.$swal({
                            type: 'error',
                            title: res.data.errors[0]
                        });
                }).catch(err => {
                    this.$swal({
                        type: 'error',
                        title: 'Erro!'
                    });
                });
            } else {
                this.$swal({
                    type: 'error',
                    title: this.trans('zedelivery.file_validation')
                });
            }
        },

        /**
         * Evento para checar a seleção de um arquivo
         */
        onFileChange(e) {
            var files = e.target.files;
            
            if (!files.length)
                return;

            this.selectedFile = files[0]

            this.disabled = false
        },

    }
}
</script>

<style>
.link_csv_model {
    font-size: 13px;
}
</style>