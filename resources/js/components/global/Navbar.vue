<template>

    <div>
        <b-navbar toggleable="lg" type="dark" variant="dark">
            <b-container>
                <b-navbar-brand :to="{
                    name : 'HomePage'
                }">
                    {{Globals.NAME}}
                </b-navbar-brand>

                <b-navbar-toggle target="nav-collapse"></b-navbar-toggle>

                <b-collapse id="nav-collapse" is-nav>
                    <b-navbar-nav>
                        <b-nav-item v-for="tutorial in tutorials" v-bind:key="tutorial.id" 
                        :to="{name : 'ViewTutorialsSpecific' , params : {tutorialLang : tutorial.slug}}">
                            {{tutorial.name}}
                        </b-nav-item>

                        <b-nav-item :to="{ name: 'CodeEditor'}">                
                            Code Editor 
                        </b-nav-item>

                        <b-nav-item-dropdown text="Admin">
                            <b-dropdown-item :to="{ name: 'Admin'}">Admin</b-dropdown-item>
                            <b-dropdown-item :to="{ name: 'Admin.Tutorials.Langs'}">Tutorials Languages</b-dropdown-item>
                            <b-dropdown-item :to="{ name: 'Admin.Tutorials'}">Tutorials</b-dropdown-item>
                            <b-dropdown-item href="#">RU</b-dropdown-item>
                            <b-dropdown-item href="#">FA</b-dropdown-item>
                        </b-nav-item-dropdown>
                        <!-- <b-nav-item :to="{ name: 'CodeEditor' , query : {
                                content : '<?php echo \'Hello World\';',
                                mode : 'php',
                                theme : 'base16-dark'
                            }}" >Disabled
                        </b-nav-item> -->
                    </b-navbar-nav>

                    <!-- Right aligned nav items -->
                    <!-- <b-navbar-nav class="ml-auto">
                        <b-nav-form>
                            <b-form-input size="sm" class="mr-sm-2" placeholder="Search"></b-form-input>
                            <b-button size="sm" class="my-2 my-sm-0" type="submit">Search</b-button>
                        </b-nav-form>

                        <b-nav-item-dropdown text="Lang" right>
                            <b-dropdown-item href="#">EN</b-dropdown-item>
                            <b-dropdown-item href="#">ES</b-dropdown-item>
                            <b-dropdown-item href="#">RU</b-dropdown-item>
                            <b-dropdown-item href="#">FA</b-dropdown-item>
                        </b-nav-item-dropdown>

                        <b-nav-item-dropdown right>
                            <template v-slot:button-content>
                                <em>User</em>
                            </template>
                            <b-dropdown-item href="#">Profile</b-dropdown-item>
                            <b-dropdown-item href="#">Sign Out</b-dropdown-item>
                        </b-nav-item-dropdown>
                    </b-navbar-nav> -->
                </b-collapse>

            </b-container>

        </b-navbar>
    </div>
</template>

<script>
export default {
    name : 'Navbar',
    data(){
        return{

        }
    },
    created(){
        this.$store.dispatch('getTutorialsLanguages');
    },
    methods : {

    },
    computed : {
        Globals(){
            return this.$store.getters.Globals;
        },
        tutorials(){
            return this.$store.getters.TutorailsLanguages
        }
    }
}
</script>