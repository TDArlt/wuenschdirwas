<template>
  <q-page class="bg-blue-grey" padding>
    <div class="fit row bg-white shadow-2 rounded-borders">
      <div class="col q-pa-md" v-if="thereAreWishes">
        TODO: Filter<br />
        TODO: Kind beim Wunsch-Erstellen/-Bearbeiten mit angeben


        <q-list separator >
          <q-expansion-item
            expand-separator dense
            v-model="filterOpened"
            label="Liste filtern nach..."
            header-class="text-bold text-primary"
          >
            <q-item class="q-pt-sm q-pb-md">
              <q-item-section class="q-pl-lg">
                <q-item-label v-if="mayEdit" class="row">
                  <q-checkbox class="col-3" dense v-model="showFulfilled" label="Erfüllte Wünsche" @update:model-value="updateFilterCookies()" />
                  <q-checkbox class="col-3" dense v-model="showInvisible" label="Ausgeblendete Wünsche" @update:model-value="updateFilterCookies()" />
                </q-item-label>
                <q-item-label class="row">
                  <template v-for="person in allPersons" :key="person.id">
                    <q-checkbox
                      dense v-model="filterPersons['p' + person.id]"
                      :label="person.name"
                      @update:model-value="updateFilterCookies()"
                      class="col-3"
                      :style="'color:' + person.color"
                      />
                  </template>
                </q-item-label>
              </q-item-section>
            </q-item>
          </q-expansion-item>

          <template v-for="(wish, index) in wishlist">
            <q-item v-if="mayShowWish(wish)" :key="wish.id" class="row q-pa-xs">
              <template v-if="!wish.dummy">

                <q-item-section class="q-pa-sm">
                  <q-item-label class="text-primary" style="font-size: 18px; font-weight: bold;">
                    {{wish.name}}
                  </q-item-label>

                  <q-item-label>
                    <q-chip dense square :label="connectedPerson(wish.person).name" :style="'margin-left: 0px; background-color:'+ connectedPerson(wish.person).color" />
                  </q-item-label>

                  <q-item-label caption style="margin-top:12px">
                    <p style="white-space: pre-wrap;">{{wish.description}}</p>
                    <p v-if="wish.price != '' && wish.price != '€ 0'">
                      Preis ca.: {{wish.price}}
                    </p>
                    <p style="margin-bottom:2px" v-if="wish.links.length > 0">
                      <q-btn v-for="link in wish.links"
                        :key="wish.id + '_' + link"
                        outline no-caps size="sm"
                        class="q-mr-sm"
                        @click="openWindow(link)"
                        >
                        Beispiel
                        <q-icon name="open_in_new" class="q-ml-xs" size="14px" />
                        <q-tooltip bottom>
                          {{link}}
                        </q-tooltip>
                      </q-btn>
                    </p>
                  </q-item-label>




                  <div class="q-mt-xs" v-if="!mayEdit && !wish.fulfilled">
                    <q-btn v-if="!wish.reserved"
                      class="q-mr-sm q-mb-sm" size="sm" color="green" icon="library_add_check"
                      label="Erfüllen"
                      @click="fulfillWish(wish.id);">
                      <q-tooltip bottom>
                        Damit wird der Wunsch für alle anderen ausgeblendet.
                      </q-tooltip>
                    </q-btn>
                    <q-btn v-else
                      class="q-mr-sm q-mb-sm" size="sm" color="green" icon="library_add_check"
                      disabled label="Erfüllen"
                      >
                      <q-tooltip bottom>
                        Der Wunsch ist reserviert. Um ihn zu erfüllen, muss erst die Reservierung entfernt werden.
                      </q-tooltip>
                    </q-btn>

                    <q-btn v-if="!wish.reserved"
                      class="q-mr-sm q-mb-sm" size="sm" color="blue" icon="collections_bookmark"
                      label="Reservieren"
                      @click="reserveWish(wish.id);">
                      <q-tooltip bottom>
                        Das Element wird gekennzeichnet, aber nicht von der Liste entfernt.
                      </q-tooltip>
                    </q-btn>
                    <q-btn v-else-if="!wish.fulfilled"
                      class="q-mr-sm q-mb-sm" size="sm" color="blue" icon="collections_bookmark"
                      label="Reservierung entfernen"
                      @click="unreserveWish(wish.id);">
                      <q-tooltip bottom>
                        Die Kennzeichnung wird entfernt.
                      </q-tooltip>
                    </q-btn>
                  </div>
                  
                  <div class="q-mt-sm" v-if="mayEdit">
                    <q-btn class="q-mr-sm q-mb-sm"
                      size="sm" color="primary" icon="edit"
                      label="Bearbeiten"
                      @click="showEditWish(wish.id)" />
                      
                    <q-btn class="q-mr-sm q-mb-sm"
                      v-if="index > 0"
                      dense
                      size="sm" color="primary" icon="arrow_drop_up"
                      @click="changeSort(index, false)">
                      <q-tooltip bottom>
                        Nach oben verschieben
                      </q-tooltip>
                    </q-btn>
                    
                    <q-btn class="q-mr-sm q-mb-sm"
                      v-if="index < wishlist.length - 1"
                      dense
                      size="sm" color="primary" icon="arrow_drop_down"
                      @click="changeSort(index, true)">
                      <q-tooltip bottom>
                        Nach unten verschieben
                      </q-tooltip>
                    </q-btn>
                  </div>
                  
                </q-item-section>

                <div style="position:absolute; top: 5px; right: 5px;">
                  <q-item-label>
                    <q-badge v-if="!wish.visible" color="blue" class="q-mr-xs">
                      <q-icon name="visibility_off" size="14px" class="q-mr-xs" />
                      Ausgeblendet
                    </q-badge>
                    <q-badge v-if="wish.reserved && !wish.fulfilled" color="red" class="q-mr-xs">
                      <q-icon name="collections_bookmark" size="14px" class="q-mr-xs" />
                      Reserviert
                    </q-badge>
                    <q-badge v-if="wish.fulfilled" color="green" class="q-mr-xs">
                      <q-icon name="library_add_check" size="14px" class="q-mr-xs" />
                      Wird erfüllt
                    </q-badge>
                  </q-item-label>
                </div>
              </template>
              <template v-else>
                <q-item-section class="q-pa-sm">
                    <q-skeleton width="100px" height="20px" class="q-mb-sm" />
                    <q-skeleton height="150px" class="q-mb-sm" />
                    <q-skeleton width="150px" class="q-mb-sm" />
                </q-item-section>

                <div style="position:absolute; top: 5px; right: 5px;">
                  <q-item-label>
                    <q-badge v-if="!wish.visible" color="blue" class="q-mr-xs">
                      <q-icon name="visibility_off" size="14px" class="q-mr-xs" />
                      <q-skeleton type="text" width="40px" />
                    </q-badge>
                    <q-badge v-if="wish.reserved && !wish.fulfilled" color="red" class="q-mr-xs">
                      <q-icon name="collections_bookmark" size="14px" class="q-mr-xs" />
                      <q-skeleton type="text" width="40px" />
                    </q-badge>
                    <q-badge v-if="wish.fulfilled" color="green" class="q-mr-xs">
                      <q-icon name="library_add_check" size="14px" class="q-mr-xs" />
                      <q-skeleton type="text" width="40px" />
                    </q-badge>
                  </q-item-label>
                </div>
              </template>
            </q-item>
          </template>
        </q-list>
      </div>


      <div v-else class="col q-pa-md">
        <q-chip color="primary" text-color="white" icon="info_outline" size="md">
          Oha! Scheinbar ist die Wunschliste aktuell leer!
        </q-chip>
      </div>

      
          <q-page-sticky v-if="mayEdit" position="bottom-right" :offset="fabPos">
            <q-btn
              round
              size="lg"
              icon="add"
              color="accent"
              :disable="draggingFab"
              v-touch-pan.prevent.mouse="moveFab"
              @click="onAddClick"
            />
          </q-page-sticky>
      
    </div>

    <q-dialog
      v-model="showEdit" persistent
      transition-show="scale" transition-hide="scale"
      >
      <q-card>
        <q-card-section>
          <div class="text-h6">Wunsch bearbeiten</div>
        </q-card-section>

        <q-card-section class="q-pt-none">
          <q-input v-model="editableWish.name"
            ref="editName"
            filled type="text" label="Name" class="q-mb-sm"
            :rules="[val => !!val || 'Bitte einen Namen angeben!']"
            />
          <q-select
            v-model="editableWish.selectedperson"
            :options="personDropdown" class="q-mb-sm" 
            label="Der Wunsch ist für..." filled />
          <q-input v-model="editableWish.description"
            ref="editDescription"
            filled type="textarea" class="q-mb-sm" label="Beschreibung" autogrow
            :rules="[val => !!val || 'Bitte eine Beschreibung angeben!']"
            />
          <q-input v-model="editableWish.price"
            mask="€ #" fill-mask="0" reverse-fill-mask filled clearable
            class="q-mb-sm q-pb-sm"
            label="Preis ca." />

          <template v-for="(url, index) in editableWish.links" :key="index">
            <q-input filled class="q-mb-sm"
              v-model="editableWish.links[index]" type="url" label="Link" clearable 
              @clear="editableWish.links.splice(index, 1)"
              />
          </template>
          <q-btn color="primary" size="sm" no-caps class="full-width q-mt-sm q-mb-sm"
            icon="add" label="Link hinzufügen"
            @click="editableWish.links.push('')" />

          <q-toggle v-model="editableWish.visible" label="Sichtbar" />
          <q-toggle v-model="editableWish.fulfilled" label="Als erfüllt markieren" />
          <q-toggle v-model="editableWish.reserved" label="Als reserviert markieren" />
          
        </q-card-section>

        <q-card-actions side align="right" class="bg-white text-teal">
          <q-btn flat label="Abbrechen" icon="cancel" v-close-popup />
          <q-btn
            v-if="!editableWish.isNew"
            flat label="Löschen" icon="delete" color="red" @click="deleteElement(editableWish.id)" />
          <q-btn flat label="Speichern" icon="cloud_done" @click="saveWishFromEditDialog()" />
        </q-card-actions>
      </q-card>
    </q-dialog>

  </q-page>
</template>

<script>
import { defineComponent } from 'vue'

export default defineComponent({
  name: 'IndexPage',
  data() {
    return {
      //linkUrl: './xchange.php',
      linkUrl: 'https://wunschliste.arlt.io/statics/xchange.php',


      wishlist: [],

      mayEdit: false,

      editableWish: [],
      showEdit: false,

      allPersons: [],
      personDropdown: [],
      defaultPerson: {},

      filterPersons: {

      },

      showFulfilled: true,
      showInvisible: true,

      
      fabPos: [ 18, 18 ],
      draggingFab: false,

      accessToken: '',

      filterOpened: true,
    }
  },

  computed: {
      thereAreWishes: function() {
        if (this.wishlist.length == 0)
        {
          return false;
        } else if (!this.mayEdit)
        {
          let count = 0;
          this.wishlist.forEach(wish => {
            if (!wish.fulfilled && wish.visible)
            {
              count++;
            }
          });

          return (count > 0);
        } else
        {
          return true;
        }
      }
  },

  mounted()
  {
    this.accessToken = this.$q.cookies.get('access_token');
    if (this.$q.cookies.has('showFulfilled'))
    {
      this.showFulfilled = this.$q.cookies.get('showFulfilled') == "true";
      this.showInvisible = this.$q.cookies.get('showInvisible') == "true";
    }
    this.loadData();
  },

  methods: {

    async loadData() {
      this.$q.loading.show();

      // Create dummy content
      for (let index = 1; index <= 20; index++)
      {
        this.wishlist.push (
          {
            id: index,
            name: 'Wunsch ' + index,
            description: 'Beschreibung',
            price: '€ ' + Math.round(Math.random()*20),
            links: ['http://www.arlt.io', 'http://www.cf-studios.com'],
            visible: (Math.random() > .2),
            reserved: (Math.random() > .7),
            fulfilled: (Math.random() > .8),
            position: index * 10,
            
            dummy: true
          }
        );
      }

      // Load user rights
      let verifyPostParam = {
        type: 'verify',
        token: this.accessToken
      }
      let verifyInfo = await this.$axios.post(this.linkUrl, verifyPostParam);
      this.mayEdit = verifyInfo.data.may_edit;

      // Load users
      this.allPersons = (await this.$axios.post(this.linkUrl, {
        type: 'persons',
        token: this.accessToken
      })).data;

      this.filterPersons = {};
      this.personDropdown = [];

      if (this.$q.cookies.has('filterPersons'))
      {
        this.filterPersons = this.$q.cookies.get('filterPersons');
      }

      for (let index = 0; index < this.allPersons.length; index++)
      {
        if (!('p' + this.allPersons[index].id in this.filterPersons))
        {
          this.filterPersons['p' + this.allPersons[index].id] = true;
        }

        this.personDropdown.push({
          label: this.allPersons[index].name,
          color: this.allPersons[index].color,
          value: this.allPersons[index].id,
        });

        if (index == 0)
        {
          this.defaultPerson = {
            label: this.allPersons[index].name,
            color: this.allPersons[index].color,
            value: this.allPersons[index].id,
          };
        }
      }






      // Load real content
      let postParam = {
        type: 'list',
        token: this.accessToken
      }
      let info = await this.$axios.post(this.linkUrl, postParam);
      this.wishlist = info.data;

      this.wishlist.sort(this.sortWishes);

      this.$q.loading.hide();
    },

    sortWishes(elementA, elementB)
    {
      return elementA.position - elementB.position;
    },


    onAddClick ()
    {
      this.showEditWish(-1);
    },

    moveFab (ev) {
      this.draggingFab = ev.isFirst !== true && ev.isFinal !== true

      this.fabPos = [
        this.fabPos[0] - ev.delta.x,
        this.fabPos[1] - ev.delta.y
      ]
    },

    openWindow(url)
    {
      window.open(url, "_blank");
    },


    connectedPerson(userId)
    {
      for (let index = 0; index < this.allPersons.length; index++)
      {
        if (this.allPersons[index].id == userId)
        {
          return this.allPersons[index];
        }
      }

      return {
        name: "",
        color: "#fff",
      }
    },

    mayShowWish(wish)
    {
      if ((!wish.fulfilled && wish.visible) || this.mayEdit)
      {
        if ((this.showInvisible || wish.visible) && (this.showFulfilled || !wish.fulfilled))
        {
          return (this.filterPersons['p' + wish.person]);
        }
      }


      return false;

    },

    updateFilterCookies()
    {
      this.$q.cookies.set('showFulfilled', this.showFulfilled, { expires: 3000 });
      this.$q.cookies.set('showInvisible', this.showInvisible, { expires: 3000 });
      this.$q.cookies.set('filterPersons', this.filterPersons, { expires: 3000 });
    },



    showEditWish(id)
    {
      let highestPosition = 0;
      let isNew = false;
      if (id == -1)
      {
        let highestId = 0;
        this.wishlist.forEach(wish => {
          if (wish.id > highestId)
          {
            highestId = wish.id;
          }
          if (wish.position > highestPosition)
          {
            highestPosition = wish.position;
          }
        });

        id = highestId + 1;
        isNew = true;
      }


      this.editableWish = {
        id: id,
        name: '',
        description: '',
        price: '',
        links: [],
        visible: true,
        reserved: false,
        fulfilled: false,
        position: highestPosition + 10,
        person: 1,
        selectedperson: JSON.parse(JSON.stringify(this.defaultPerson)),
        
        dummy: false,
        isNew: isNew
      };

      let foundWish = this.getWishById(id);
      if (foundWish != null)
      {
        this.editableWish = JSON.parse(JSON.stringify(foundWish));

        for (let index = 0; index < this.personDropdown.length; index++)
        {
          if (this.editableWish.person == this.personDropdown[index].value)
          {
            this.editableWish.selectedperson = this.personDropdown[index];
            break;
          }
          
        }
      }

      this.showEdit = true;


    },

    saveWishFromEditDialog()
    {
      this.$refs.editName.validate();
      this.$refs.editDescription.validate();

      if (this.$refs.editName.hasError || this.$refs.editDescription.hasError)
      {
        this.$q.notify({
          color: 'negative',
          message: 'Bitte überprüfe deine Angaben!'
        });
        return;
      }

      this.editableWish.person = this.editableWish.selectedperson.value;

      for (let index = 0; index < this.editableWish.links.length; index++)
      {
        if (this.editableWish.links[index].trim() == '')
        {
          this.editableWish.links.splice(index, 1);
          index--;
        } else if (!this.editableWish.links[index].startsWith("http"))
        {
          this.editableWish.links[index] = "http://" + this.editableWish.links[index];
        }
      }

      if (this.editableWish.isNew)
      {
        this.wishlist.push(JSON.parse(JSON.stringify(this.editableWish)));
        this.updateElement(this.editableWish.id);
      } else
      {
        for (let index = 0; index < this.wishlist.length; index++)
        {
          if (this.wishlist[index].id == this.editableWish.id)
          {
            this.wishlist[index] = JSON.parse(JSON.stringify(this.editableWish));
            this.updateElement(this.wishlist[index].id);
          }
        }
      }

      this.$q.notify({
        message: 'Alles klar, "' + this.editableWish.name + '" wurde gespeichert.',
        color: 'positive',
        progress: true,
        multiLine: true,
        icon: 'cloud_done',
      });
      
      this.showEdit = false;
    },










    reserveWish(id)
    {
      let wish = this.getWishById(id);

      this.$q.dialog({
        title: 'Möchtest du "' + wish.name + '" reservieren?',
        message: 'Der Wunsch wird dann gekennzeichnet, aber es kann auch jeder noch die Kennzeichnung entfernen',
        cancel: true,
        persistent: true,
        ok: 'Ja',
        cancel: 'Nein'
      }).onOk(() => {
        wish.reserved = true;
        this.updateElement(id);

        this.$q.notify({
          message: 'Alles klar, "' + wish.name + '" ist jetzt als "reserviert" gekennzeichnet.',
          color: 'blue',
          progress: true,
          multiLine: true,
          icon: 'collections_bookmark',
        });
      });
    },

    unreserveWish(id)
    {
      let wish = this.getWishById(id);

      this.$q.dialog({
        title: 'Reservierung für "' + wish.name + '" aufheben?',
        message: 'Wir wissen nicht, ob die Kennzeichnung von dir stammte, aber damit wird sie jedenfalls entfernt.',
        cancel: true,
        persistent: true,
        ok: 'Ja',
        cancel: 'Nein'
      }).onOk(() => {
        wish.reserved = false;
        this.updateElement(id);
        
        this.$q.notify({
          message: 'Alles klar, für "' + wish.name + '" wurde die Reservierung aufgehoben.',
          color: 'blue',
          progress: true,
          multiLine: true,
          icon: 'collections_bookmark',
        });
      });
    },

    fulfillWish(id)
    {
      let wish = this.getWishById(id);
      
      this.$q.dialog({
        title: '"' + wish.name + '" erfüllen?',
        message: 'Der Wunsch wird dann ausgeblendet. Wenn du möchtest, kannst du ihn dir per E-Mail schicken lassen, indem du deine Adresse hier eingibst (deine Adresse wird nicht gespeichert):',
        cancel: true,
        persistent: true,
        prompt: {
          model: '',
          type: 'text',
          isValid: val => (val.trim() == '' || this.validateEmail(val)),
        },
        ok: 'Ja',
        cancel: 'Nein'
      }).onOk(data => {
        wish.fulfilled = true;
        this.updateElement(id);

        
        this.$q.notify({
          message: 'Alles klar, "' + wish.name + '" wurde jetzt als "erfüllt" gekennzeichnet.',
          color: 'green',
          progress: true,
          multiLine: true,
          icon: 'library_add_check',
        });

        // Send mail...
        let wishMessage = 'Hallo,\ndu hast dir gerade folgenden Wunsch auf unserer Webseite zum Erfüllen ausgesucht:\n' +
          wish.name + '\n' + wish.description + '\nPreis ca.: ' + wish.price + '\n';
        
        if (wish.links.length > 0)
        {
          wishMessage += 'Beispiel-Links:\n';
          wish.links.forEach(link => {
            wishMessage += link + '\n';
          });
        }

        wishMessage += '\nVielen Dank schon einmal dafür!';
        
        let postParam = {
          type: 'mail',
          token: this.accessToken,
          subject: 'Wünsch dir was Webseite: Dein zu erfüllender Wunsch',
          message: wishMessage,
          to: data
        }
        this.$axios.post(this.linkUrl, postParam).then(received => {
          if (received.data.success === true)
          {
            this.$q.notify({
              message: 'Du hast auch eine E-Mail an ' + data + ' bekommen (prüfe eventuell deinen Spam-Ordner)',
              color: 'green',
              progress: true,
              multiLine: true,
              icon: 'mail',
            });
          } else
          {
            this.$q.notify({
              message: 'Leider konnten wir keine Mail an ' + data + ' verschicken. Falls das ein Problem ist, melde dich bitte bei uns!',
              color: 'red',
              progress: true,
              multiLine: true,
              icon: 'unsubscribe',
            });
          }
        });
      });

    },


    async changeSort(arrayPos, downwards)
    {
      let newPos = arrayPos + 1;
      if (!downwards)
      {
        newPos = arrayPos - 1;
      }
      

      let otherElement = this.wishlist[newPos];
      let otherPosition = otherElement.position;
      let thisElement = this.wishlist[arrayPos];
      let thisPosition = thisElement.position;

      thisElement.position = otherPosition;
      otherElement.position = thisPosition;


      
      this.wishlist.sort(this.sortWishes);

      await this.updateElement(thisElement.id);
      await this.updateElement(otherElement.id);

      this.$q.notify({
          message: 'Reihenfolge geändert.',
          color: 'green',
          progress: true,
          icon: 'sort',
        });
    },
    
    deleteElement(id)
    {
      let wish = this.getWishById(id);
      let wishKey = -1;
      for (let index = 0; index < this.wishlist.length; index++)
      {
        if (this.wishlist[index].id == id)
        {
          wishKey = index;
        }
        
      }



      this.$q.dialog({
        title: '"' + wish.name + '" wirklich löschen?',
        message: 'Der Wunsch und alle Informationen werden dabei endgültig entfernt.',
        cancel: true,
        persistent: true,
        ok: 'Ja, löschen',
        cancel: 'Nein'
      }).onOk(() => {

        this.wishlist.splice(wishKey, 1);

        this.deleteElementInDb(id);
        

        this.$q.notify({
          message: 'Der Wunsch "' + wish.name + '" wurde entfernt.',
          color: 'red',
          progress: true,
          icon: 'delete',
        });

        this.showEdit = false;
      });

    },




    async updateElement(id)
    {
      let wish = this.getWishById(id);
      wish.isNew = false;

      let postParam = {
        type: 'update',
        token: this.accessToken,
        id: wish.id,
        name: wish.name,
        description: wish.description,
        price: wish.price,
        links: JSON.stringify(wish.links),
        visible: wish.visible,
        reserved: wish.reserved,
        fulfilled: wish.fulfilled,
        position: wish.position,
        person: wish.person,
      }
      
      let info = await this.$axios.post(this.linkUrl, postParam);

      return info.success;
    },


    async deleteElementInDb(id)
    {
      let postParam = {
        type: 'delete',
        token: this.accessToken,
        id: id
      }
      let info = await this.$axios.post(this.linkUrl, postParam);

      return info.success;
    },









    getWishById(id)
    {
      let outputWish;

      this.wishlist.forEach(wish => {
        if (wish.id == id)
        {
          outputWish = wish;
        }
      });

      return outputWish;
    },

    validateEmail(email) 
    {
        var re = /\S+@\S+\.\S+/;
        return re.test(email);
    }
  }

})
</script>
