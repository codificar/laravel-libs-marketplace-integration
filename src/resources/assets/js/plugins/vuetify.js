import Vue from "vue";
import 'vuetify/dist/vuetify.min.css'
import Vuetify, { 
    VApp,
    VAppBar, 
    VNavigationDrawer,
    VToolbar,
    VContainer,
    VMain,
    VAppBarNavIcon,
    VToolbarTitle,
    VFooter,
    VListItem,
    VListItemContent,
    VListItemTitle,
    VListItemSubtitle,
    VListItemIcon,
    VDivider,
    VList,
    VSelect,
    VIcon,
    VCard,
    VSwitch,
    VRow,
    VCol,
    VBtn,
    VCardTitle,
    VAvatar,
    VCardText,
    VForm,
    VTextField,
    VCheckbox,
    VImg,
    VSheet,
    VSkeletonLoader,
    VBottomSheet,
    VFabTransition,
    VSpeedDial,
    VListItemGroup
 } from "vuetify/lib";

 Vue.use(Vuetify, {
    components: {
      VApp,
      VAppBar,
      VNavigationDrawer,
      VToolbar,
      VContainer,
      VMain,
      VAppBarNavIcon,
      VToolbarTitle,
      VFooter,
      VListItem,
      VListItemContent,
      VListItemTitle,
      VListItemSubtitle,
      VListItemIcon,
      VDivider,
      VList,
      VIcon,
      VSelect,
      VCard,
      VSwitch,
      VRow,
      VCol,
      VBtn,
      VCardTitle,
      VAvatar,
      VCardText,
      VForm,
      VTextField,
      VCheckbox,
      VImg,
      VSheet,
      VSkeletonLoader,
      VBottomSheet,
      VFabTransition,
      VSpeedDial,
      VListItemGroup
    },
 })

const opts = {
  theme:{
    dark: false
  },
  
}

 export default new Vuetify(opts)