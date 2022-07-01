<template>
    <div>
        <h3>Permisos Específicos</h3>
        <v-layout row wrap>
            <v-flex sm2
                v-for="item in user_permissions"
                :key="'p'+item.name"
            >
                <v-switch
                    v-show="showSwitch(item)"
                    @change="setPermisosUsr"
                    v-model="permissions_selected"
                    :label="item.nombre"
                    :value="item.name"
                    color="success">
                ></v-switch>
            </v-flex>
        </v-layout>
    </div>
</template>
<script>
import {mapGetters} from 'vuex';
export default {
    props:{
        user: Object,
        user_permissions: Array,
    },
    data(){
        return{
            permissions_selected:[],
            nombres:[],
            show: false
        }
    },
    mounted(){
        axios.get('/admin/users/'+this.user.id+'/permissions')
            .then(res => {

                this.permissions_selected = res.data.user_permissions;
            })
            .catch(err => {
                this.$toast.error(err.response.data.message);
                this.$router.push({ name: 'users'})
            })

    },
    computed: {
        ...mapGetters([
            'isRoot'
        ]),
    },
    methods:{
        showSwitch(item){
            if (item.name == "harddel" && !this.isRoot){
                return false;
            }
            return true;
        },
        setPermisosUsr(){

                axios.put('/admin/users/'+this.user.id+'/permissions',
                        {
                            permissions: this.permissions_selected
                    })
                    .then(response => {
                        this.$toast.success(response.data);
                    })
                    .catch(err => {

                        const msg_valid = err.response.data.errors;
                        for (const prop in msg_valid) {
                            this.$toast.error(`${msg_valid[prop]}`);
                        }
                    });
            },
    }
}
</script>

