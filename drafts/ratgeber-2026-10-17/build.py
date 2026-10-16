# Erzeugt den PHP-Snippet, der /ratgeber/ + 4 Artikel (ohne Insulinspritze, Nutzerentscheidung 2026-10-17) als WP-Seiten anlegt/aktualisiert.
import json, sys, base64
from artikel import ARTIKEL
skip = {'insulinspritze-einheiten-umrechnen'}
arts = []
for i, x in enumerate([a for a in ARTIKEL if a['slug'] not in skip]):
    body = x['body'].replace('<table class="rg-tab">', '<div class="tw"><table class="rg-tab">').replace('</table>', '</table></div>')
    arts.append({'slug': x['slug'], 'title': x['title'], 'excerpt': x['lead'], 'content': body.strip(), 'seo': [x['seo_title'], x['meta']], 'faq': [list(f) for f in x['faq']], 'order': i + 1})
idx = {'slug': 'ratgeber', 'title': 'Ratgeber für die Peptidforschung',
       'excerpt': 'Anleitungen, Vergleiche und Hintergrundwissen rund um Forschungspeptide – sachlich erklärt, ohne Fachchinesisch.',
       'seo': ['Ratgeber – Peptide verständlich erklärt', 'Der Peptrium-Ratgeber: Peptide rekonstituieren, Reinheit per HPLC und LC-MS verstehen, Wirkstoffe vergleichen und sicher einkaufen – sachlich erklärt.']}
data = base64.b64encode(json.dumps({'idx': idx, 'arts': arts}, ensure_ascii=False).encode()).decode()
php = r'''
$d = json_decode(base64_decode('%s'), true);
function sp_rg_upsert($slug, $parent, $title, $excerpt, $content, $order) {
    $ex = get_posts(['post_type'=>'page','name'=>$slug,'post_parent'=>$parent,'post_status'=>'any','numberposts'=>1]);
    $arr = ['post_type'=>'page','post_status'=>'publish','post_name'=>$slug,'post_parent'=>$parent,'post_title'=>$title,
            'post_excerpt'=>$excerpt,'post_content'=>$content,'menu_order'=>$order,'comment_status'=>'closed','ping_status'=>'closed'];
    if ($ex) $arr['ID'] = $ex[0]->ID;
    $id = wp_insert_post(wp_slash($arr), true);
    if (is_wp_error($id)) { echo $slug, ' FEHLER ', $id->get_error_message(), "\n"; return 0; }
    foreach (['site-content-layout'=>'page-builder','ast-site-content-layout'=>'full-width-container','site-content-style'=>'unboxed','site-sidebar-layout'=>'no-sidebar','site-post-title'=>'disabled','ast-featured-img'=>'disabled'] as $k=>$v) update_post_meta($id, $k, $v);
    return $id;
}
$i = $d['idx'];
$pid = sp_rg_upsert($i['slug'], 0, $i['title'], $i['excerpt'], '', 0);
update_post_meta($pid, '_sp_seo', $i['seo']);
echo "ratgeber $pid ", get_permalink($pid), "\n";
foreach ($d['arts'] as $a) {
    $id = sp_rg_upsert($a['slug'], $pid, $a['title'], $a['excerpt'], $a['content'], $a['order']);
    update_post_meta($id, '_sp_seo', $a['seo']);
    update_post_meta($id, '_sp_rg_faq', $a['faq']);
    echo $id, ' ', get_permalink($id), ' ', strlen(get_post_field('post_content', $id)), '/', strlen($a['content']), "\n";
}
''' % data
open(sys.argv[1], 'w').write(php)
print('ok', len(arts))
