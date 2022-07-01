<template>
    <div v-if="registros">
        <my-dialog :dialog.sync="dialog" registro="registro" @destroyReg="destroyReg"></my-dialog>
        <v-card>
            <v-card-title>
                <h2>{{ titulo }}</h2>
                <v-spacer></v-spacer>
                <menu-ope></menu-ope>
            </v-card-title>
        </v-card>
        <v-card>
            <v-container>
                <v-layout row wrap>
                    <v-flex xs6></v-flex>
                    <v-flex xs6>
                        <v-spacer></v-spacer>
                        <v-text-field v-model="search" append-icon="search" label="Buscar" single-line hide-details></v-text-field>
                    </v-flex>
                </v-layout>
                <v-layout row wrap>
                    <v-flex xs12>
                        <v-data-table
                            :headers="headers"
                            :pagination.sync="pagination"
                            :items="bancos"
                            :search="search"
                            rows-per-page-text="Registros por página"
                        >
                            <template slot="items" slot-scope="props">
                                <td>{{ props.item.entidad }}</td>
                                <td>{{ props.item.nombre }}</td>
                                <td>{{ props.item.bic }}</td>
                                <td class="justify-center layout px-0">
                                    <v-icon small class="mr-2" @click="editItem(props.item.id)">
                                        edit
                                    </v-icon>
                                    <v-icon small @click="openDialog(props.item.id)">
                                        delete
                                    </v-icon>
                                </td>
                            </template>
                            <template slot="pageText" slot-scope="props">
                                Registros {{ props.pageStart }} - {{ props.pageStop }} de {{ props.itemsLength }}
                            </template>
                        </v-data-table>
                    </v-flex>
                </v-layout>
            </v-container>
        </v-card>
    </div>
</template>
<script>
import MyDialog from '@/components/shared/MyDialog';
import MenuOpe from './MenuOpe';
export default {
    components: {
        'my-dialog': MyDialog,
        'menu-ope': MenuOpe,
    },
    data() {
        return {
            pagination: {
                model: 'bancos',
                descending: false,
                page: 1,
                rowsPerPage: 10,
                sortBy: 'entidad',
            },

            search: '',
            headers: [
                {
                    text: 'Entidad',
                    align: 'left',
                    value: 'entidad',
                },
                {
                    text: 'Nombre',
                    align: 'left',
                    value: 'nombre',
                },
                {
                    text: 'BIC',
                    align: 'left',
                    value: 'BIC',
                },
                {
                    text: 'Acciones',
                    align: 'Center',
                    value: '',
                },
            ],
            bancos: [],
            status: false,
            registros: false,
            dialog: false,
            banco_id: 0,
            titulo: 'Entidades/Bancos',
        };
    },
    mounted() {
        axios
            .get('/mto/bancos')
            .then((res) => {
                this.bancos = res.data;
                this.registros = true;
            })
            .catch((err) => {
                this.$toast.error(err.response.data.message);
                this.$router.push({ name: 'dash' });
            });
    },
    methods: {
        create() {
            this.$router.push({ name: 'banco.create' });
        },
        editItem(id) {
            this.$router.push({ name: 'banco.edit', params: { id: id } });
        },
        openDialog(id) {
            this.dialog = true;
            this.banco_id = id;
        },
        destroyReg() {
            this.dialog = false;

            axios
                .post('/mto/bancos/' + this.banco_id, { _method: 'delete' })
                .then((response) => {
                    if (response.status == 200) {
                        this.$toast.success('Ubicación eliminada!');
                        this.bancos = response.data;
                    }
                })
                .catch((err) => {
                    this.status = true;

                    var msg = err.response.data.message;
                    this.$toast.error(msg);
                });
        },
    },
};
</script>
