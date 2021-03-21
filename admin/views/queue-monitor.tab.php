<?php
    wp_enqueue_script ( 'vuejs' );
    wp_enqueue_script ( 'vue-pagination' );
    wp_enqueue_script ( 'vue-queue-monitor' );
?>
<div id="queue-monitor">

    <p><?php echo __('Monitoring a customer carts queue', 'agora-abandoned-cart'); ?></p>
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
        <b v-if="count > -1"><?php echo __('Count', 'agora-abandoned-cart'); ?>: {{ count }}</b>
    </div>
    <table class="wp-list-table widefat fixed striped table-view-list pages" width="100%">
        <thead>
            <tr>
                <th style="width: 100px"><?php echo __('Queue Id', 'agora-abandoned-cart'); ?></th>
                <th style="width: 250px"><?php echo __('User Id', 'agora-abandoned-cart'); ?></th>
                <th><?php echo __('Notify driver', 'agora-abandoned-cart'); ?></th>
                <th><?php echo __('Status', 'agora-abandoned-cart'); ?></th>
                <th><?php echo __('Added', 'agora-abandoned-cart'); ?></th>
                <th><?php echo __('Updated', 'agora-abandoned-cart'); ?></th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="item in list" :key="item.id" v-if="!isLoading">
                <td>{{ item.id }}</td>
                <td><a :href="'user-edit.php?user_id=' + item.user_id" target="_blank">{{ item.username }}</a></td>
                <td class="notify-driver-cell">
                  
                <template v-if="item.status == 'process'">
                    <i class="spinner is-active spin"></i> <?php echo __('Sending ...', 'agora-abandoned-cart'); ?>
                </template>
                <template v-if="item.status != 'process'">
                    <span v-if="item.email_notification == 1" class="dashicons dashicons-yes-alt icon-success" title="Email"></span>
                    <span v-if="item.email_notification == 0" class="dashicons dashicons-no-alt icon-failad" title="Email"></span>
                    <span v-if="item.email_notification == ''" class="dashicons dashicons-warning" title="Email"></span>
                    <?php echo __('Email notification', 'agora-abandoned-cart'); ?>
                    <br />
                    <span v-if="item.push_notification == 1" class="dashicons dashicons-yes-alt icon-success" title="Push notification"></span>
                    <span v-if="item.push_notification == 0" class="ashicons dashicons-no-alt icon-failad" title="Push notification"></span>
                    <span v-if="item.push_notification == ''" class="dashicons dashicons-warning" title="Push notification"></span>
                    <?php echo __('Push notification', 'agora-abandoned-cart'); ?>
                  </template>
                </td>
                <td><span :class="statusClass(item)">{{ statuses[item.status] }}</status></td>
                <td>{{ item.created_at}}</td>
                <td>{{ getItemUpdated(item)}}</td>
            </tr>
            <tr v-if="list.length == 0 && !isLoading">
              <td colspan="6"><?php echo __('No records', 'agora-abandoned-cart'); ?></td>
            </tr>
            <tr v-if="isLoading">
              <td colspan="6" class="text-center warning">
                <i class="spinner is-active spin"></i>
              </td>
            </tr>
            
        </tbody>
    </table>
    <paginate :count="count" :pages="pages" :current="current" @navigate="loadPage"></paginate>
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