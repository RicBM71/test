<template>
    <div>
        <v-card>
            <loading :show_loading="show_loading"></loading>
            <v-card-title color="indigo">
                <h2 color="indigo">Recogidas</h2>
                <v-spacer></v-spacer>
                <v-tooltip bottom>
                    <template v-slot:activator="{ on }">
                        <v-btn v-show="items.length > 0" v-on="on" color="white" icon @click="show_filtro = !show_filtro">
                            <v-icon color="primary">filter_list</v-icon>
                        </v-btn>
                    </template>
                    <span>Selección</span>
                </v-tooltip>
                <v-tooltip bottom>
                    <template v-slot:activator="{ on }">
                        <v-btn v-show="items.length > 0" v-on="on" color="white" icon @click="goExcel()">
                            <v-avatar size="32px">
                                <img class="img-fluid" src="/assets/excel.png" />
                            </v-avatar>
                        </v-btn>
                    </template>
                    <span>Exportar a Excel</span>
                </v-tooltip>
            </v-card-title>
        </v-card>
        <v-card>
            <v-form>
                <v-container>
                    <v-layout row wrap v-show="show_filtro">
                        <v-spacer></v-spacer>
                        <v-flex sm2>
                            <v-switch label="Todas las tiendas" v-model="todas" color="primary"> ></v-switch>
                        </v-flex>
                        <v-flex sm2>
                            <v-switch label="Sin Recoger" v-model="norecogidas" color="primary"> ></v-switch>
                        </v-flex>
                        <v-flex md2 sm2 xs6>
                            <v-menu
                                v-model="menu_d"
                                :close-on-content-click="false"
                                :nudge-right="40"
                                lazy
                                transition="scale-transition"
                                offset-y
                                full-width
                                min-width="290px"
                            >
                                <v-text-field
                                    slot="activator"
                                    :value="computedFechaD"
                                    label="Recogida Desde"
                                    append-icon="event"
                                    v-validate="'date_format:dd/MM/yyyy'"
                                    data-vv-name="fecha_d"
                                    :error-messages="errors.collect('fecha_d')"
                                    data-vv-as="Desde"
                                    readonly
                                ></v-text-field>
                                <v-date-picker
                                    v-model="fecha_d"
                                    no-title
                                    locale="es"
                                    first-day-of-week="1"
                                    @input="menu_d = false"
                                ></v-date-picker>
                            </v-menu>
                        </v-flex>
                        <v-flex md2 sm2 xs6>
                            <v-menu
                                v-model="menu_h"
                                :close-on-content-click="false"
                                :nudge-right="40"
                                lazy
                                transition="scale-transition"
                                offset-y
                                full-width
                                min-width="290px"
                            >
                                <v-text-field
                                    slot="activator"
                                    :value="computedFechaH"
                                    label="Hasta"
                                    append-icon="event"
                                    v-validate="'date_format:dd/MM/yyyy'"
                                    data-vv-name="fecha_h"
                                    :error-messages="errors.collect('fecha_h')"
                                    data-vv-as="Hasta"
                                    readonly
                                ></v-text-field>
                                <v-date-picker
                                    v-model="fecha_h"
                                    no-title
                                    locale="es"
                                    first-day-of-week="1"
                                    @input="menu_h = false"
                                ></v-date-picker>
                            </v-menu>
                        </v-flex>
                        <v-flex md2 sm2 xs12>
                            <v-btn @click="submit" :loading="show_loading" flat round small block color="info">
                                Enviar
                            </v-btn>
                        </v-flex>
                    </v-layout>
                    <v-layout row wrap v-if="items.length > 0">
                        <v-flex xs6></v-flex>
                        <v-flex xs6>
                            <v-spacer></v-spacer>
                            <v-text-field v-model="search" append-icon="search" label="Buscar" single-line hide-details></v-text-field>
                        </v-flex>
                    </v-layout>
                    <v-layout row wrap v-if="items.length > 0">
                        <v-flex xs12>
                            <v-data-table
                                :headers="headers"
                                :search="search"
                                :items="items"
                                :pagination.sync="pagination"
                                rows-per-page-text="Registros por página"
                            >
                                <template slot="items" slot-scope="props">
                                    <tr>
                                        <td>{{ props.item.empresa.razon }}</td>
                                        <td>{{ albser(props.item) }}</td>
                                        <td class="text-xs-right">
                                            {{
                                                props.item.importe
                                                    | currency('€', 2, {
                                                        thousandsSeparator: '.',
                                                        decimalSeparator: ',',
                                                        symbolOnLeft: false,
                                                    })
                                            }}
                                        </td>
                                        <td>{{ props.item.cliente.razon }}</td>
                                        <td>{{ formatDate(props.item.fecha_compra) }}</td>
                                        <td>{{ formatDate(props.item.fecha_recogida) }}</td>
                                        <td>{{ props.item.fase.nombre }}</td>
                                        <td>{{ extraerClases(props.item.allcomlines) }}</td>
                                        <td>
                                            <v-icon
                                                v-if="hasEdtUbi && props.item.fase_id == 4"
                                                small
                                                class="mr-2"
                                                @click="clearDate(props.item)"
                                            >
                                                error
                                            </v-icon>
                                        </td>
                                    </tr>
                                </template>
                            </v-data-table>
                        </v-flex>
                    </v-layout>
                </v-container>
            </v-form>
        </v-card>
    </div>
</template>
<script>
import moment from 'moment';
import { mapGetters } from 'vuex';
import Loading from '@/components/shared/Loading';
export default {
    $_veeValidate: {
        validator: 'new',
    },
    components: {
        loading: Loading,
    },
    data() {
        return {
            search: '',
            norecogidas: false,
            headers: [
                {
                    text: 'Empresa',
                    align: 'left',
                    value: 'empresa_id',
                },
                {
                    text: 'Compra',
                    align: 'left',
                    value: 'com_ser',
                },
                {
                    text: 'Importe',
                    align: 'left',
                    value: 'importe',
                },
                {
                    text: 'Cliente',
                    align: 'left',
                    value: 'cliente.razon',
                },
                {
                    text: 'F. Compra',
                    align: 'left',
                    value: 'fecha_compra',
                },
                {
                    text: 'F. Recogida',
                    align: 'left',
                    value: 'fecha_recogida',
                },
                {
                    text: 'Fase',
                    align: 'left',
                    value: 'fase.nombre',
                },
                {
                    text: 'Clase',
                    align: 'left',
                    value: 'id',
                },
            ],

            show_loading: false,
            ejercicio: new Date().toISOString().substr(0, 4),
            show_filtro: true,
            items: [],
            //fecha_d: new Date().toISOString().substr(0, 7)+"-01",
            fecha_d: new Date().toISOString().substr(0, 10),
            fecha_h: new Date().toISOString().substr(0, 10),
            menu_d: false,
            menu_h: false,
            total: 0,
            todas: true,
            pagination: {
                rowsPerPage: 10,
                sortBy: 'empresa_id',
                descending: false,
            },
        };
    },
    mounted() {
        var fecha = new Date();

        fecha.setDate(fecha.getDate() + 1);
        fecha = fecha.toISOString().substr(0, 10);

        this.fecha_d = fecha;
        this.fecha_h = fecha;
    },
    computed: {
        ...mapGetters(['hasEdtUbi']),
        computedFechaD() {
            moment.locale('es');
            return this.fecha_d ? moment(this.fecha_d).format('L') : '';
        },
        computedFechaH() {
            moment.locale('es');
            return this.fecha_h ? moment(this.fecha_h).format('L') : '';
        },
    },
    methods: {
        albser(item) {
            return item.serie_com + item.albaran + '-' + moment(item.fecha_compra).format('YY');
        },
        formatDate(f) {
            if (f == null) return null;
            moment.locale('es');
            return moment(f).format('DD/MM/YYYY');
        },
        extraerClases(items) {
            const clase = items.map(function(item) {
                return item.clase.nombre;
            });

            return _.uniqBy(clase).toString();
        },
        clearDate(item) {
            this.editedIndex = this.items.indexOf(item);
            this.editedItem = Object.assign({}, item);
            axios.put('/exportar/recogidas/' + item.id + '/update', { fecha_recogida: null }).then((res) => {
                this.items.splice(this.editedIndex, 1);
                this.$toast.success(res.data.message);
            });
        },
        submit() {
            if (this.show_loading === false) {
                this.show_loading = true;
                this.$validator.validateAll().then((result) => {
                    if (result) {
                        axios({
                            url: '/exportar/recogidas',
                            method: 'POST',
                            data: {
                                fecha_d: this.fecha_d,
                                fecha_h: this.fecha_h,
                                norecogidas: this.norecogidas,
                                todas: this.todas,
                            },
                        })
                            .then((res) => {
                                this.items = res.data;

                                if (this.items.length > 0) this.show_filtro = false;
                                else this.$toast.warning('No hay registros!');
                            })
                            .catch((err) => {
                                if (err.request.status == 422) {
                                    // fallo de validated.
                                    const msg_valid = err.response.data.errors;
                                    for (const prop in msg_valid) {
                                        // this.$toast.error(`${msg_valid[prop]}`);

                                        this.errors.add({
                                            field: prop,
                                            msg: `${msg_valid[prop]}`,
                                        });
                                    }
                                } else {
                                    this.$toast.error(err.response.data.message);
                                }
                            })
                            .finally(() => {
                                this.show_loading = false;
                            });
                    }
                });
            }
        },
        goExcel() {
            this.show_loading = true;
            axios({
                url: '/exportar/recogidas/excel',
                method: 'POST',
                responseType: 'blob', // important
                data: {
                    fecha_d: this.fecha_d,
                    fecha_h: this.fecha_h,
                    norecogidas: this.norecogidas,
                    todas: this.todas,
                },
            })
                .then((response) => {
                    let blob = new Blob([response.data]);
                    let link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);

                    link.download = 'Recogidas.' + new Date().getFullYear() + (new Date().getMonth() + 1) + new Date().getDate() + '.xlsx';

                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);

                    this.$toast.success('Download Ok! ' + link.download);

                    this.show_loading = false;
                })
                .catch((err) => {
                    this.$toast.error(err.response.data.message);
                    this.show_loading = false;
                });
        },
    },
};
</script>
