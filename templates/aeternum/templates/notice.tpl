<li class="h-entry notice post notice-source-web" id="notice-{$notice->uid}">
   <section class="notice-headers">
      <a href="{$title->link->url}" class="u-uid">
      {$title->content}
      </a>
      <a href="{$poster->profileLink}" title="{$poster->nickname} >
      <img src="{$poster->avatar}" title="{$poster->name}'s avatar" /> {$poster->name}
      </a>
      
      {if ($notice->hasParents==true)}
      <div class="parents">
      in reply to <a href="$notice->parent->url" class="u-in-reply-to" rel="in-reply-to"><ul class="addressees">
      {foreach $notice->parents as parent}
      <li><a href="{$parent}">{$parent["text"]}</a></li>
      {/foreach}
      </ul></a>
      </div>
      {/if}
   </section>
   <article class="{$notice->nameClass} e-content">
   {$notice->content}
   </article>
   <footer>
      <a rel="bookmark" class="timestamp" href="{$conversation->href}">In conversation</a>
      <time class="dt-published" datetime="{$dt->iso}" title="{$dt->exact}">{$dt->approximate}</time>
      <span class="source">
      from <span class="device">web</span>
      </span>
      <a href="https://aeternum.highlandarrow.com/notice/1" class="permalink u-url">permalink</a>
   </footer>
</li>