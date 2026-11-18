from gen import *
def m_wd():
    th=T('dark','#000000');L=utm('/produkt/cagrilintid/','wieder-da-cagri')
    return wrap2(img('nl-bw-wd-cagri.jpg',L,'Cagrilintid ist wieder da',th)
      +R(P('Viele haben gefragt – <b style="color:#FFFFFF;">Cagrilintid 10 mg ist wieder auf Lager</b> und sofort versandfertig. Und weil gerade Black Week ist, bekommst du es direkt <b style="color:#FFFFFF;">30 % günstiger</b>.',th,15),'22px 26px 6px',th)
      +R(checks(['Ohne Code – der Rabatt ist schon im Preis','Bac Water und Spritzen gleich mitbestellen – auch −30 %','Ab 200 € gibt es das Injektionskit gratis dazu','Versand in 2 Werktagen, diskret verpackt'],th),'14px 26px 4px',th)
      +R(btn('Cagrilintid sichern',L,th),'20px 22px 6px',th)
      +R(P('Die Menge ist begrenzt – beim letzten Mal war es schnell weg.',th,12.5,c=th['mu']),'12px 26px 26px',th),th)
if __name__=='__main__':
    b=m_wd();open('wd-cagri.body','w').write(b)
    open('vorschau-wd-cagri.html','w').write('<!doctype html><meta charset="utf-8"><meta name="viewport" content="width=device-width"><body style="margin:0">'+b.replace(E,'')+'</body>')
