<template>
    <div>
        <loading :show_loading="show_loading"></loading>
        <div v-if="registros">
            <v-card>
                <v-card-title>
                    <h2>{{ titulo }}</h2>
                    <v-spacer></v-spacer>
                    <v-tooltip bottom>
                        <template v-slot:activator="{ on }">
                            <v-btn v-on="on" color="white" icon @click="filtro = !filtro">
                                <v-icon color="primary">filter_list</v-icon>
                            </v-btn>
                        </template>
                        <span>Filtrar</span>
                    </v-tooltip>
                </v-card-title>
            </v-card>
            <v-card v-show="filtro">
                <filtro-hcom :filtro.sync="filtro" :reg.sync="items"></filtro-hcom>
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
                    <br />
                    <v-layout row wrap>
                        <v-flex xs12>
                            <v-data-table
                                :headers="headers"
                                :items="items"
                                :search="search"
                                :expand="expand"
                                rows-per-page-text="Registros por página"
                            >
                                <template slot="items" slot-scope="props">
                                    <td>{{ getDni(props.item.cliente.dni) }}</td>
                                    <td>{{ props.item.cliente.razon }}</td>
                                    <td>{{ props.item.alb_ser }}</td>
                                    <td>{{ formatDate(props.item.fecha_compra) }} {{ props.item.tipo.nombre[0] }}</td>
                                    <td class="text-xs-right">
                                        {{
                                            props.item.importe
                                                | currency('€', 2, { thousandsSeparator: '.', decimalSeparator: ',', symbolOnLeft: false })
                                        }}
                                    </td>
                                    <td v-if="parametros.doble_interes == true && props.item.tipo_id == 1" class="text-xs-center">
                                        {{
                                            props.item.interes
                                                | currency('', 0, { thousandsSeparator: '.', decimalSeparator: ',', symbolOnLeft: false })
                                        }}/{{
                                            props.item.interes_recuperacion
                                                | currency('', 0, { thousandsSeparator: '.', decimalSeparator: ',', symbolOnLeft: false })
                                        }}
                                    </td>
                                    <td v-if="parametros.doble_interes == false && props.item.tipo_id == 1" class="text-xs-center">
                                        {{
                                            props.item.interes
                                                | currency('', 2, { thousandsSeparator: '.', decimalSeparator: ',', symbolOnLeft: false })
                                        }}
                                    </td>
                                    <td v-if="props.item.tipo_id != 1" class="text-xs-center">-</td>
                                    <td v-if="props.item.fase_id == 4">{{ props.item.retraso }}</td>
                                    <td v-else>-</td>
                                    <td :class="props.item.fase.color">{{ props.item.fase.nombre }}</td>
                                    <td class="justify-center layout px-0">
                                        <v-icon v-show="props.item.notas > ''" small class="mr-2" @click="props.expanded = !props.expanded">
                                            visibility
                                        </v-icon>
                                        <v-icon small class="mr-2" @click="editItem(props.item)">
                                            push_pin
                                        </v-icon>
                                    </td>
                                </template>
                                <template v-slot:expand="props">
                                    <v-card flat>
                                        <v-card-text class="font-italic">
                                            {{ props.item.notas }}
                                        </v-card-text>
                                    </v-card>
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
    </div>
</template>
<script>
import moment from 'moment';
import Loading from '@/components/shared/Loading';
import FiltroHcom from './FiltroHcom';
import { mapGetters } from 'vuex';

export default {
    components: {
        loading: Loading,
        FiltroHcom,
    },
    data() {
        return {
            expand: false,
            titulo: 'Histórico Compras',
            compra: {
                id: 0,
                cliente_id: 0,
            },
            paginaActual: {},
            pagination: {
                model: 'hcompra',
                descending: true,
                page: 1,
                rowsPerPage: 10,
                sortBy: 'eje_alb',
            },
            search: '',
            headers: [
                {
                    text: 'NIF/NIE',
                    align: 'left',
                    value: 'cliente.dni',
                },
                {
                    text: 'Nombre y Apellidos',
                    align: 'left',
                    value: 'cliente.razon',
                    width: '30%',
                },
                {
                    text: 'Número',
                    align: 'left',
                    value: 'eje_alb',
                    width: '18%',
                },
                {
                    text: 'F. Compra',
                    align: 'left',
                    value: 'fecha_compra',
                    width: '20%',
                },
                {
                    text: 'Importe',
                    align: 'left',
                    value: 'importe',
                },
                {
                    text: '% Ren',
                    align: 'left',
                    value: 'interes',
                    width: '2%',
                },
                {
                    text: 'Ret',
                    align: 'left',
                    value: 'retraso',
                    width: '1%',
                },
                {
                    text: 'Fase',
                    align: 'left',
                    value: 'fase.nombre',
                },
                {
                    text: 'Acciones',
                    align: 'Center',
                    value: '',
                },
            ],
            items: [],
            status: false,
            registros: false,
            dialog: false,
            item_destroy: {},
            show_loading: true,
            url: '/compras/hcompras',
            ruta: 'hcompra',

            filtro: false,
        };
    },
    mounted() {
        axios
            .get(this.url)
            .then((res) => {
                this.items = res.data;
                this.registros = true;
                this.show_loading = false;
            })
            .catch((err) => {
                this.$toast.error(err.response.data.message);
                this.$router.push({ name: 'dash' });
            });
    },
    computed: {
        ...mapGetters(['hasEdtCli', 'parametros']),
    },
    methods: {
        getDni(dni) {
            return this.hasEdtCli ? dni : '******' + dni.substr(-4);
        },
        formatDate(f) {
            if (f == null) return null;
            moment.locale('es');
            return moment(f).format('DD/MM/YYYY');
        },
        editItem(item) {
            this.$router.push({ name: 'hcompras.show', params: { id: item.id } });
        },
    },
};
</script>
