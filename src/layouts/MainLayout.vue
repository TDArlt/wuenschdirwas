<template>
  <q-layout view="hHh LpR fFf">
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
        <q-btn
          v-else
          flat
          dense
          round
          icon="menu"
          aria-label="Menu"
          disable
        />
        <q-toolbar-title style="text-align: center;">
          <q-avatar circle outline size="sm">
            <img src="~assets/favicon.svg">
          </q-avatar>
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
      <q-list separator>
          
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
      <template v-else-if="!loadingState">
        <q-page class="bg-blue-grey row justify-center items-center" padding>

          <q-card bordered class="q-pa-lg shadow-1">
            <q-card-section>
              <div class="text-h6">Login</div>
              <div class="q-gutter-md q-mt-sm">
                <q-input
                  v-model="password" filled :type="isPwd ? 'password' : 'text'"
                  dense required
                  label="Passwort"
                  v-on:keyup.enter="login">
                  <template v-slot:append>
                    <q-icon
                      :name="isPwd ? 'visibility_off' : 'visibility'"
                      class="cursor-pointer"
                      @click="isPwd = !isPwd"
                    />
                  </template>
                </q-input>
              </div>
            </q-card-section>
            <q-card-actions class="q-px-md">
              <q-btn @click="login" dense unelevated color="blue-grey-6" size="m" class="full-width" label="Login" type="submit"/>
            </q-card-actions>

          </q-card>
        </q-page>
      </template>
      <template v-else>
        <q-page class="bg-blue-grey row justify-center items-center" padding>
          <q-spinner-puff color="white" size="xl" />
        </q-page>
      </template>
    </q-page-container>
  </q-layout>
</template>

<script>
import { defineComponent, ref } from 'vue'

export default defineComponent({
  name: 'MainLayout',

  data () {
    return {
      //linkUrl: './xchange.php',
      linkUrl: 'https://wunschliste.arlt.io/statics/xchange.php',

      loadingState: true,

      leftDrawerOpen: false,
      
      loggedIn: false,

      password: '',
      isPwd: true,

      accessToken: ''
    }
  },

  mounted()
  {
    this.loadState();
  },

  methods: {
    async loadState() {

      if (this.$q.cookies.has('access_token'))
      {
        this.accessToken = this.$q.cookies.get('access_token');

        let postParam = {
          type: 'verify',
          token: this.accessToken
        }
        let info = await this.$axios.post(this.linkUrl, postParam);

        if (info.success === false)
        {
          this.accessToken = '';
          this.loggedIn = false;
        } else
        {
          this.loggedIn = true;
        }
      } else
      {
        this.loggedIn = false;
      }

      
      this.loadingState = false;
    },


    async login() {

      this.$q.loading.show();
      this.leftDrawerOpen = false;
      
      let postParam = {
        type: 'login',
        password: this.password
      }
      let info = await this.$axios.post(this.linkUrl, postParam);


      let correct = info.data.success === true;
      if (correct)
      {
        this.password = '';
        this.$q.notify({
          message: 'Erfolgreich angemeldet!',
          color: 'green',
          progress: true,
          icon: 'check',
        });

        // Set cookie
        this.$q.cookies.set('access_token', info.data.token, { expires: 30 });
        this.loggedIn = true;

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
      // Clear cookie
      this.$q.cookies.remove('access_token');
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
        
        let postParam = {
          type: 'mail',
          token: this.accessToken,
          subject: 'Wünsch dir was Webseite: Frage',
          message: 'Hallo,\nfolgende Frage ist gerade über die Wünsch-dir-was-Seite eingegangen:\n' + data
        }
        this.$axios.post(this.linkUrl, postParam).then(received => {
          if (received.data.success === true)
          {
            this.$q.notify({
              message: 'Alles klar, deine Frage wurde verschickt!',
              color: 'green',
              progress: true,
              multiLine: true,
              icon: 'mail',
            });
          } else
          {
            this.$q.notify({
              message: 'Leider konnten wir deine Frage gerade nicht verschicken (Webserver kaputt). Bitte versuche es auf einem anderen Weg!',
              color: 'red',
              progress: true,
              multiLine: true,
              icon: 'unsubscribe',
            });
          }
        });
      });
    },
  }
})
</script>
