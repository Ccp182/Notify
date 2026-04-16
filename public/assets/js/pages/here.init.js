const platform = new H.service.Platform({
    'apikey': 'jJ_fKxO35v1gzc0kKadiNTMTmsz9MAW8Qf6KkXbOxmc'
});

// Obtain the default map types from the platform object:
const defaultLayers = platform.createDefaultLayers();

// Instantiate (and display) a map:
var map = new H.Map(
    document.getElementById("map"),
    defaultLayers.vector.normal.map, {
        zoom: 6,
        //center: { lat: -2.152925152545262, lng: -79.89407124461324 },
        pixelRatio: window.devicePixelRatio || 1
    });

// MapEvents enables the event system
// Behavior implements default interactions for pan/zoom (also on mobile touch environments)
const behavior = new H.mapevents.Behavior(new H.mapevents.MapEvents(map));
var ui = H.ui.UI.createDefault(map, defaultLayers);

window.onload = function () {
    addMarkersToMap(map);
  
}

function addMarkersToMap(map) {
    const group = new H.map.Group();
    map.addObject(group);
    $.each(listVehTable, function(key, item) {
        const latitud = parseFloat(item.GeoX);
        const longitud = parseFloat(item.GeoY);
       // const icon = new H.map.Icon("https://res.24hm.net/Gestor/images/icons/Markers/"+item.Icon+".png");
        const marker = new H.map.Marker({ lat: latitud, lng: longitud },/* { icon: icon }*/);
        map.addObject(marker);
        group.addObject(marker);
        markers[item.Vid] = {
            marker: marker
        };
    });

    const provider = new H.clustering.Provider({
        clusteringOptions: {
            eps: 32,
            minWeight: 2
        },
        data: group.getObjects()
    });

    const clusteringLayer = new H.map.layer.ObjectLayer(provider);

    map.addLayer(clusteringLayer);
}
