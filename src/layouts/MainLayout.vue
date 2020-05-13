<template>
  <q-layout view="hHh lpR fFf">
    <q-header elevated>
      <q-toolbar>
        <q-btn
          v-if="loggedIn"
          flat
          dense
          round
          icon="menu"
          aria-label="Menu"
          @click="leftDrawerOpen = !leftDrawerOpen"
        />

        <q-toolbar-title>
          Wünsch dir was
        </q-toolbar-title>
      </q-toolbar>
    </q-header>

    <q-drawer
      v-if="loggedIn"
      v-model="leftDrawerOpen"
      show-if-above
      elevated
      content-class="bg-grey-1"
    >
      <q-list>
          
          <q-item
            v-if="loggedIn"
            clickable ripple
            @click="contact()"
          >
            <q-item-section
              avatar
            >
              <q-icon name="mail" />
            </q-item-section>

            <q-item-section>
              <q-item-label>Frage an Mama & Papa stellen</q-item-label>
            </q-item-section>
          </q-item>





          <q-item
            v-if="!loggedIn"
            clickable ripple
            @click="login()"
          >
            <q-item-section
              avatar
            >
              <q-icon name="person" />
            </q-item-section>

            <q-item-section>
              <q-item-label>Anmelden</q-item-label>
            </q-item-section>
          </q-item>
          <q-item
            v-if="loggedIn"
            clickable ripple
            @click="logout()"
          >
            <q-item-section
              avatar
            >
              <q-icon name="exit_to_app" />
            </q-item-section>

            <q-item-section>
              <q-item-label>Abmelden</q-item-label>
            </q-item-section>
          </q-item>
      </q-list>
    </q-drawer>

    <q-page-container>
      <template v-if="loggedIn">
        <router-view />
      </template>
      <template v-else>
        <q-page class="bg-blue-grey row justify-center items-center" padding>

          <q-card bordered class="q-pa-lg shadow-1">
            <q-card-section>
              <div class="text-h6">Login</div>
              <q-form class="q-gutter-md">
                <q-input dense clearable v-model="password" type="password" label="Passwort" required />
              </q-form>
            </q-card-section>
            <q-card-actions class="q-px-md">
              <q-btn @click="login" dense unelevated color="blue-grey-6" size="m" class="full-width" label="Login" type="submit"/>
            </q-card-actions>

          </q-card>
        </q-page>
      </template>
    </q-page-container>
  </q-layout>
</template>

<script>
import EssentialLink from 'components/EssentialLink'

export default {
  name: 'MainLayout',

  data () {
    return {
      //linkUrl: './statics/xchange.php',
      linkUrl: 'http://localhost/wuenschdirwas/src/statics/xchange.php',

      leftDrawerOpen: false,
      
      loggedIn: false,

      password: ''
    }
  },

  mounted()
  {
    this.loadState();
  },

  methods: {
    async loadState() {
      this.$q.loading.show();


      
      this.$q.loading.hide();
    },


    async login() {

      this.$q.loading.show();
      this.leftDrawerOpen = false;
      
      let postParam = {
        type: 'login',
        password: this.password
      }
      let info = await this.$axios.post(this.linkUrl, postParam);


      this.loggedIn = info.data.success === true;
      if (this.loggedIn)
      {
        this.password = '';
        this.$q.notify({
          message: 'Erfolgreich angemeldet!',
          color: 'green',
          progress: true,
          icon: 'check',
        });

        //TODO: Set cookie

      } else
      {
        this.$q.notify({
          message: 'Falsches Passwort!',
          color: 'red',
          progress: true,
          icon: 'error',
        });
      }

      
      this.$q.loading.hide();
    },

    async logout() {
      // TODO: Clear cookie
      this.loggedIn = false;


        this.$q.notify({
          message: 'Erfolgreich abgemeldet!',
          color: 'green',
          progress: true,
          icon: 'check',
        });
    },

    contact() {
      this.$q.dialog({
        title: 'Frage stellen',
        message: 'Schreib uns hier deine Frage. Vergiss nicht, vielleicht auch dazu zu schreiben, wer du bist, damit wir sie beantworten können ;-)',
        cancel: true,
        persistent: true,
        prompt: {
          model: '',
          type: 'textarea',
          isValid: val => (val.trim() != ''),
        },
        ok: 'Frage schicken',
        cancel: 'Abbrechen'
      }).onOk(data => {
        

        //TODO: Send mail...

        
        this.$q.notify({
          message: 'Alles klar, deine Frage wurde verschickt.',
          color: 'green',
          progress: true,
          multiLine: true,
          icon: 'mail',
        });
      });
    },
  }
}
</script>
