<template>
    <div v-show="show">
        <v-card>
            <v-card-title color="indigo">
                <h2 color="indigo">{{ titulo }}</h2>
                <v-spacer></v-spacer>
                <menu-ope :id="banco.id"></menu-ope>
            </v-card-title>
        </v-card>
        <v-card>
            <v-form>
                <v-container>
                    <v-layout row wrap>
                        <v-flex sm1></v-flex>
                        <v-flex sm1>
                            <v-text-field
                                v-model="banco.entidad"
                                v-validate="'required|max:4'"
                                :error-messages="errors.collect('entidad')"
                                label="entidad"
                                data-vv-name="entidad"
                                data-vv-as="entidad"
                                required
                                v-on:keyup.enter="submit"
                            >
                            </v-text-field>
                        </v-flex>
                        <v-flex sm4>
                            <v-text-field
                                v-model="banco.nombre"
                                v-validate="'required'"
                                :error-messages="errors.collect('nombre')"
                                label="Nombre"
                                data-vv-name="nombre"
                                data-vv-as="nombre"
                                required
                                v-on:keyup.enter="submit"
                            >
                            </v-text-field>
                        </v-flex>
                        <v-flex sm2>
                            <v-text-field
                                v-model="banco.bic"
                                v-validate="'required|max:12'"
                                :error-messages="errors.collect('bic')"
                                label="bic"
                                data-vv-name="bic"
                                data-vv-as="bic"
                                required
                                v-on:keyup.enter="submit"
                            >
                            </v-text-field>
                        </v-flex>
                    </v-layout>
                    <v-layout row wrap>
                        <v-flex sm1></v-flex>
                        <v-flex sm2>
                            <v-text-field v-model="computedFModFormat" label="Modificado" readonly> </v-text-field>
                        </v-flex>
                        <v-flex sm2>
                            <v-text-field v-model="computedFCreFormat" label="Creado" readonly> </v-text-field>
                        </v-flex>
                        <v-flex sm1></v-flex>
                        <v-flex sm1>
                            <div class="text-xs-center">
                                <v-btn @click="submit" round :loading="loading" block color="primary">
                                    Guardar
                                </v-btn>
                            </div>
                        </v-flex>
                    </v-layout>
                </v-container>
            </v-form>
        </v-card>
    </div>
</template>
<script>
import moment from 'moment';
import MenuOpe from './MenuOpe';

export default {
    $_veeValidate: {
        validator: 'new',
    },
    components: {
        'menu-ope': MenuOpe,
    },
    data() {
        return {
            titulo: 'Bancos',
            banco: {
                id: 0,
                nombre: '',
                entidad: '',
                bic: '',
                updated_at: '',
                created_at: '',
            },

            status: false,
            loading: false,

            show: false,
        };
    },
    mounted() {
        var id = this.$route.params.id;

        if (id > 0)
            axios
                .get('/mto/bancos/' + id + '/edit')
                .then((res) => {
                    this.banco = res.data.banco;
                    this.show = true;
                })
                .catch((err) => {
                    this.$toast.error(err.response.data.message);
                    this.$router.push({ name: 'banco.index' });
                });
    },
    computed: {
        computedFModFormat() {
            moment.locale('es');
            return this.banco.updated_at ? moment(this.banco.updated_at).format('D/MM/YYYY H:mm') : '';
        },
        computedFCreFormat() {
            moment.locale('es');
            return this.banco.created_at ? moment(this.banco.created_at).format('D/MM/YYYY H:mm') : '';
        },
    },
    methods: {
        submit() {
            if (this.loading === false) {
                this.loading = true;

                var url = '/mto/bancos/' + this.banco.id;
                var metodo = 'put';
                this.$validator.validateAll().then((result) => {
                    if (result) {
                        axios({
                            method: metodo,
                            url: url,
                            data: {
                                nombre: this.banco.nombre,
                                entidad: this.banco.entidad,
                                bic: this.banco.bic,
                            },
                        })
                            .then((response) => {
                                this.$toast.success(response.data.message);
                                this.banco = response.data.banco;
                                this.loading = false;
                            })
                            .catch((err) => {
                                if (err.request.status == 422) {
                                    // fallo de validated.
                                    const msg_valid = err.response.data.errors;
                                    for (const prop in msg_valid) {
                                        this.errors.add({
                                            field: prop,
                                            msg: `${msg_valid[prop]}`,
                                        });
                                    }
                                } else {
                                    this.$toast.error(err.response.data.message);
                                }
                                this.loading = false;
                            });
                    } else {
                        this.loading = false;
                    }
                });
            }
        },
    },
};
</script>
