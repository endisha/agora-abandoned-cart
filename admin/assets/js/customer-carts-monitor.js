var app = new Vue({
    el: '#customer-carts-monitor',
    data: {
      title: 'Customer Carts',
      list: [],
      cartProducts: [],
      cartTotal: 0,
      currency: '',
      cartOpening: false,
      cartOpeningId: 0,
      count: -1,
      current: 1,
      pages: 0,
      isLoading: false,
      statuses: {abondoned: 'Abondoned', recoverd: 'Recoverd', canceled: 'Canceled', pending: 'Pending', completed: 'Completed'},
      filters: {
        status: '',
        username: ''
      }
    },
    mounted() {
      this.loadList();
    },
    computed: {
      hasFilters() {
        return this.filters.status != '' || this.filters.username != '';
      }
    },
    methods: {
      loadList() {
        var that = this;
        this.isLoading = true;
        jQuery.post(agoraAbandonedCart.url, {nonce: agoraAbandonedCart.nonce, action: 'getMonitorCartsData', page: this.current, filters: this.filters}, function(data){
          that.isLoading = false;
          if (data.data.success) {
            that.list = data.data.list;
            that.count = data.data.count;
            that.current = data.data.current;
            that.pages = data.data.pages;
          } else {
            //TODO: error
          }
        },'JSON');
      },
      getCartDetails(id, username) {
        var that = this;
        this.cartOpening = true;
        this.cartOpeningId = id;
        jQuery.post(agoraAbandonedCart.url, {nonce: agoraAbandonedCart.nonce, action: 'getCartDetails', id: id}, function(data){
          that.cartOpening = false;
          if (data.data.success) {
            if(data.data.products.length > 0){
              that.cartProducts = data.data.products;
              that.cartTotal = data.data.total;
              that.currency = data.data.currency;

              var popupModal = jQuery( "#view-cart-modal" ).dialog({
                  title: "Cart Details: #" + id + " [username: " + username + "]",
                  autoOpen: false,
                  height: 400,
                  width: 600,
                  modal: true
                });
                popupModal.parent().fadeIn(300);
                popupModal.dialog( "open" );
            }else{
              that.cartOpening = false;
            }
          } else {
            that.cartOpening = false;
          }
        },'JSON');
      },
      getItemUpdated(item) {
        if (item.created_at == item.updated_at) {
          return '';
        } else {
          return item.updated_at;
        }
      },

      loadPage(page) {
        this.current = page;
        this.loadList();
      },

      statusClass(item) {
        cls = 'default';
        switch(item.status) {
          case 'pending':
            cls = 'pending'; break;
          case 'recoverd':
            cls = 'success'; break;
          case 'abondoned':
            cls = 'process'; break;
          case 'canceled':   
            cls = 'failed'; break; 
          case 'completed':
            cls = 'completed'; break; 
          
        }
        return 'label label-' + cls;
      },

      filterList() {
        this.current = 1;
        this.loadList();
      },
      filterReset() {
        this.current = 1;
        this.filters= { status: '', username: ''};
        this.loadList();
      }
    },
  })