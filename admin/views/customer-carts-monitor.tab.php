<?php
    wp_enqueue_script ( 'vuejs' );
    wp_enqueue_script ( 'vue-pagination' );
    wp_enqueue_script ( 'vue-customer-carts-monitor' );
?>
<div id="customer-carts-monitor">

    <p>
        <?php echo __('Show Customers Abandoned/recoverd Carts logs', 'agora-abandoned-cart'); ?>
    </p>
    <div class="tablenav top search-box">
        <div class="alignleft">
                <label for="username" class="inline-label"><?php echo __('Username', 'agora-abandoned-cart'); ?></label>
                <input type="text" v-model="filters.username" id="username" class="form-control" @keyup.enter="filterList()">
        </div>
        <div class="alignleft">
            <label for="status" class="inline-label"><?php echo __('Status', 'agora-abandoned-cart'); ?></label>
            <select v-model="filters.status" class="form-control" id="status" @keyup.enter="filterList()">
                <option value=""><?php echo __('Any', 'agora-abandoned-cart'); ?></option>
                <option v-for="(index,status) in statuses" :value="status">{{ index }}</option>
            </select>
        </div>
        <div class="alignleft">
        <label class="inline-label"></label>
            <button class="button" type="button" @click="filterList()"><?php echo __('Filter', 'agora-abandoned-cart'); ?></button>
            <button class="button reset" type="button" @click="filterReset()" v-show="hasFilters"><?php echo __('Reset', 'agora-abandoned-cart'); ?></button>
            <button class="button" type="button" @click="filterReset()">
              <span :class="{'spinner is-active spin': isLoading}"></span>
               <?php echo __('Refresh', 'agora-abandoned-cart'); ?>
            </button>
        </div>
    </div>
    <div class="form-group">
        <b v-if="count > -1">Count: {{ count }}</b>
    </div>
    <table class="wp-list-table widefat fixed striped table-view-list pages" width="100%">
        <thead>
            <tr>
                <th style="width: 100px"><?php echo __('Id', 'agora-abandoned-cart'); ?></th>
                <th style="width: 250px"><?php echo __('User', 'agora-abandoned-cart'); ?></th>
                <th><?php echo __('Cart', 'agora-abandoned-cart'); ?></th>
                <th><?php echo __('Notification', 'agora-abandoned-cart'); ?></th>
                <th><?php echo __('Status', 'agora-abandoned-cart'); ?></th>
                <th><?php echo __('Added', 'agora-abandoned-cart'); ?></th>
                <th><?php echo __('Refreshed at', 'agora-abandoned-cart'); ?></th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="item in list" :key="item.id" v-if="!isLoading">
                <td>{{ item.id }}</td>
                <td><a :href="'user-edit.php?user_id=' + item.key" target="_blank">{{ item.username }}</a></td>
                <td class="notify-driver-cell">
                  <button class="button button-small" @click="getCartDetails(item.id, item.username)" :disabled="cartOpening">
                    <i class="dashicons dashicons-cart" v-if="!(cartOpening && cartOpeningId == item.id)"></i>
                    <i class="spinner is-active spin" v-if="cartOpening && cartOpeningId === item.id"></i>
                  </button>
                </td>
                <td>
                <template v-if="item.counter > 0">
                   #{{ item.counter }}
                </template>
                <template v-else>
                 -
                </template>
              </td>
              <td><span :class="statusClass(item)">{{ statuses[item.status] }}</status></td>
              <td>{{ item.created_at }}</td>
              <td>{{ item.refreshed_at }}</td>
            </tr>
            <tr v-if="list.length == 0 && !isLoading">
              <td colspan="7"><?php echo __('No records', 'agora-abandoned-cart'); ?></td>
            </tr>
            <tr v-if="isLoading">
              <td colspan="7" class="text-center warning">
                <i class="spinner is-active spin"></i>
              </td>
            </tr>
            
        </tbody>
    </table>
    <paginate :count="count" :pages="pages" :current="current" @navigate="loadPage"></paginate>

    <div id="view-cart-modal" style="display: none;">
    <h4><?php echo __('Total', 'agora-abandoned-cart'); ?>: {{ cartTotal }} {{ currency }}</h4>
    <table class="wp-list-table widefat  striped" width="100%">
        <tbody>
            <tr v-for="item in cartProducts" :key="item.id">
                <td style="width: 50px;"><img :src="item.image" style="width: 50px;" /></td>
                <td style="vertical-align: middle;">
                  Product: <strong>{{ item.name }}</strong>
                  <br />Quantity: <strong>{{ item.quantity }}</strong>
                  <br />Price: <strong>{{ item.price }} {{ currency }}</strong>
                  <br />Subtotal: <strong>{{ item.subtotal }} {{ currency }}</strong>
                </td>
            </tr>
        </tbody>
    </table>
</div>

</div>
<script type="text/x-template" id="pagination-template">
<div v-if="pages > 1" class="row">
    <hr />
    <div class="col-sm-3" v-if="count > 0">
      {{ count }} <?php echo __('records', 'agora-abandoned-cart'); ?> / </span>
      <?php echo __('Page', 'agora-abandoned-cart'); ?> {{ current }} <?php echo __('of', 'agora-abandoned-cart'); ?> {{ pages }}
    </div>
      <span class="pagination-links">
          <template>
            <a class="button" href="#" @click="goPage(1)"><span class="screen-reader-text"><?php echo __('Previous page', 'agora-abandoned-cart'); ?></span><span aria-hidden="true">‹</span></a>
              <a href="#" :data-page="prevPage" @click="goPage(prevPage)"  class="button">
              <span aria-hidden="true">&laquo;</span>
              </a>
          </template>
          <template v-if="loopNumbers[0] > 1">
            <span>...</span>
          </template>
          <template v-for="n in loopNumbers">
            <template v-bind:class="{ active: current == n }">
                <a href="#" :data-page="n" @click="goPage(n)" class="button" style="margin-right: 2px;">
                {{ n }}
                </a> 
            </template>
          </template>
          <template v-if="loopNumbers[loopNumbers.length - 1] < pages">
            <span>...</span>
          </template>
          <template>
              <a href="#" :data-page="nextPage" @click="goPage(nextPage)" class="button">
              <span aria-hidden="true">&raquo;</span>
              </a>
          </template>
      </span>
    </div>
</script>  