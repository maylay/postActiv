<!-- begin single notice - id {$this->uid} -->

<div class="notice" id="stream-item-{$this->uid}" data-uri="{$this->uri}" data-source="{$this->source}" data-conversation-id="{$this->conversation-id}" data-in-reply-to-screen-name="{$this->in-reply-to-screen-name}" data-reply-to-profile-url="{$this->in-reply-to-profile-url}" data-in-reply-to-status-id="{$this->in-reply-to-status-id}" data-in-reply-to-ostatus-uri="{$this->in-reply-to-ostatus-uri}" data-user-screen-name="{$this->user-screen-name}" data-user-ostatus-uri="$this->user-ostatus-uri}" data-user-profile-url="{$this->data-user-profile-url}">
   <script class="attachment-json" type="application/json">{$this->attachment-json}</script>
   <script class="attentions-json" type="application/json">{$this->attentions-json}</script>
   <img class="avatar notice-size" id="notice-avatar-{$this->notice-id}" src="{$this->avatar_url}"/>

   <div class="notice-header" id="notice-header-{$this->notice-id}"><p>
   <b>{$this->user-screen-name} @{$this->user-handle}</b> posted at ${this->timestamp}{if isset($this->attentions_links)} to the attention of {$this->attentions_links}{/if}:
   </p></div>

   <div class="notice-content" id="notice-content-{$this->notice-id}"><p>
   {if $template->use_markdown==true}{$this->content|markdown nofilter}{else}{$this->content}{/if}
   </p></div>

   <div class="notice-footer" id="notice-footer-{$this->notice-id}"><p>
   {if isset($this->attachments_links)}<b>Attachments:</b> {$this->attachments_links}<br>{/if}
   <b>Permalink:</b> {$this->uri}<br/>
   </p></div>

   <div class="notice-interactions" id="notice-interactions-{$this->notice-id}">
   <img src="{$template->favorite-icon-url}" title="Favourite this notice" class="favourite-icon" />
   <img src="{$template->repeat-icon-url}" title="Repeat this notice" class="repeat-icon" />
{if ($this->is_event==true)}   <img src="{$template->rsvp-icon-url}" title="RSVP this event" class="event-rsvp-icon" />{/if}
   </div>
</div>

<!-- end single notice - id {$this->uid} -->