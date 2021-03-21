var app = new Vue({
    el: '#overview',
    data: {
      title: 'Overview',
      list: [],
      isLoading: false
    },
    mounted() {
      this.loadWidgets();
    },
    methods: {
      loadWidgets() {
        var that = this;
        this.isLoading = true;
        jQuery.post(agoraAbandonedCart.url, {nonce: agoraAbandonedCart.nonce, action: 'getOverviewDetails'}, function(data){
          that.isLoading = false;
          if (data.data.success) {
            that.list = data.data.overview_widget;
          } else {
            //TODO: error
          }
        },'JSON');
      }
    }
});