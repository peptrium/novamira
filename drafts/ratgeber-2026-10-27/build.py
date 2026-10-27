# Erzeugt den PHP-Snippet, der die 4 neuen Ratgeber-Artikel (8–11) unter /ratgeber/ anlegt/aktualisiert.
import json, sys, base64
from artikel import ARTIKEL
arts = []
for i, x in enumerate(ARTIKEL):
    body = x['body'].replace('<table class="rg-tab">', '<div class="tw"><table class="rg-tab">').replace('</table>', '</table></div>')
    arts.append({'slug': x['slug'], 'title': x['title'], 'excerpt': x['lead'], 'content': body.strip(), 'seo': [x['seo_title'], x['meta']], 'faq': [list(f) for f in x['faq']], 'order': i + 7})
data = base64.b64encode(json.dumps(arts, ensure_ascii=False).encode()).decode()
php = r'''
$arts = json_decode(base64_decode('%s'), true);
$par = get_page_by_path('ratgeber'); if (!$par) { echo 'KEIN RATGEBER'; return; } $pid = $par->ID;
foreach ($arts as $a) {
    $ex = get_posts(['post_type'=>'page','name'=>$a['slug'],'post_parent'=>$pid,'post_status'=>'any','numberposts'=>1]);
    $arr = ['post_type'=>'page','post_status'=>'publish','post_name'=>$a['slug'],'post_parent'=>$pid,'post_title'=>$a['title'],
            'post_excerpt'=>$a['excerpt'],'post_content'=>$a['content'],'menu_order'=>$a['order'],'comment_status'=>'closed','ping_status'=>'closed'];
    if ($ex) $arr['ID'] = $ex[0]->ID;
    $id = wp_insert_post(wp_slash($arr), true);
    if (is_wp_error($id)) { echo $a['slug'], ' FEHLER ', $id->get_error_message(), "\n"; continue; }
    foreach (['site-content-layout'=>'page-builder','ast-site-content-layout'=>'full-width-container','site-content-style'=>'unboxed','site-sidebar-layout'=>'no-sidebar','site-post-title'=>'disabled','ast-featured-img'=>'disabled'] as $k=>$v) update_post_meta($id, $k, $v);
    update_post_meta($id, '_sp_seo', $a['seo']);
    update_post_meta($id, '_sp_rg_faq', $a['faq']);
    echo $id, ' ', get_permalink($id), ' ', strlen(get_post_field('post_content', $id)), '/', strlen($a['content']), "\n";
}
''' % data
open(sys.argv[1], 'w').write(php)
print('ok', len(arts))
