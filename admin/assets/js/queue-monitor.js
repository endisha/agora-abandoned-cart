var app = new Vue({
    el: '#queue-monitor',
    data: {
      title: 'Renew Queue',
      list: [],
      count: -1,
      current: 1,
      pages: 0,
      isLoading: false,
      statuses: {pending: 'Pending', process: 'In Progress', success: 'Accepted', failed: 'Rejected'},
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
        jQuery.post(agoraAbandonedCart.url, {nonce: agoraAbandonedCart.nonce, action: 'getMonitorQueueData', page: this.current, filters: this.filters}, function(data){
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
          case 'success':
            cls = 'success'; break;
          case 'process':
            cls = 'process'; break;
          case 'failed':   
            cls = 'failed'; break; 
          
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