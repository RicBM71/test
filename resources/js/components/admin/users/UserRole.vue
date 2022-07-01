<template>
    <div v-show="show">
        <h3>Roles Usuario</h3>
        <v-container fluid>
            <v-layout row>
                <v-flex sm2
                    v-for="role in roles"
                    :key="role"
                >
                    <v-switch
                        @change="setUserRole"
                        v-model="user_role"
                        :label="role"
                        :value="role"
                        color="success">
                    ></v-switch>
                </v-flex>
            </v-layout>
            <!-- <v-radio-group
                v-model="user_role"
                row
                @change="setUserRole"
            >
                <v-radio
                    v-for="role in roles"
                    :label="role"
                    :value="role"
                    :key="keyRole(role)"
                >
                </v-radio>
            </v-radio-group> -->
        <v-layout row>
            <v-flex sm2>
                <h3>Heredados vía Role</h3>
            </v-flex>
        </v-layout>
        </v-container>
        <v-layout row v-if="permisos_heredados.length > 0">
            <v-flex sm12>
                <v-chip v-for="item in permisos_heredados"
                    :key="keyHeredados(item)"
                    class="caption" outline color="blue">{{item.nombre}}</v-chip>
            </v-flex>
        </v-layout>
        <v-layout row v-if="permisos_heredados.length > 0">
            <v-flex sm12>
                <user-permiso :user="user" :user_permissions="user_permissions"></user-permiso>
            </v-flex>
        </v-layout>
    </div>
</template>
<script>
import UserPermiso from './UserPermiso'
export default {
    props: ['user'],
    components: {
            'user-permiso': UserPermiso,
		},
    data () {
        return {
            roles: [],
            user_permissions: [],
            show: false,

            row: null,
            user_role: [],
            permisos_heredados: [],
        }
    },
    mounted(){

            //cargamos todos los roles disponibles
        axios.get('/admin/users/'+this.user.id+'/roles')
            .then(res => {

                this.roles = res.data.roles;
                this.permisos_heredados = res.data.permisos_heredados;
                this.user_permissions = res.data.user_permissions;

                this.user_role = res.data.user_role;

                this.show = (this.roles.length > 0 );
            })
            .catch(err => {
                this.$toast.error(err.response.data.message);
                this.$router.push({ name: 'users'})
            })

    },
    methods:{
        keyRole(role){
            return 'a_'+role;
        },
        keyHeredados(role){
            return 'h_'+role.name;
        },
                //actualizamos role x usuario
        setUserRole(){

            axios.put('/admin/users/'+this.user.id+'/roles',{
                        roles: this.user_role
                    }
                )
                .then(res => {
                    this.permisos_heredados = res.data.permisos_heredados;
                    this.user_permissions = res.data.user_permissions;
                    this.$toast.success('Role actualizado');
                })
                .catch(err => {

                    const msg_valid = err.response.data.errors;
                    for (const prop in msg_valid) {
                        this.$toast.error(`${msg_valid[prop]}`);
                    }
                });
        }
    }
}
</script>

