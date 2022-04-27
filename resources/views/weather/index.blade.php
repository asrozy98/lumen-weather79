@extends('layout.index',(['title'=> 'Weather | Lumen 79']))
@section('content')
    <div class="mt-3 p-2 row">
        <h2 class="col">Weather Now</h2>
    </div>
    <div class="bg-light row justify-content-evenly">
        <div class="col-12 mb-4">
            <div class="card bg-primary p-2">
                <div class="card-title text-white">
                    <h3 id="namePlace"></h3>
                    <h5 id="addressPlace"></h5>
                </div>
                <div class="card-body row">
                    <div class="col-6">
                        <span class="text-white font-weight-bold" style="font-size: 2rem"><span id="temperature">-</span>
                            &deg;C</span>
                        <img src="" alt="weather" class="float-right" style="height: 10rem" id="iconWeather">
                        <p class="timePlace"></p>
                    </div>
                    <div class="col-6">
                        <h6 class="card-subtitle mb-2 text-white" id="feel">-</h6>
                        <p class="text-white"><i class="bi bi-wind"></i> <span id="wind">-</span></p>
                        <p class="text-white"><i class="bi bi-moisture"></i> <span id="humidity">-</span></p>
                        <p class="text-white"><i class="bi bi-cloud-drizzle-fill"></i> <span id="rain">-</span></p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 mb-4">
            <h3>Map Location</h3>
            <div id="map" style="width:100%; height:50vh"></div>
        </div>
        <div class="col-12">
            <h3>5 Day / 3 Hour Forecast</h3>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col">Date</th>
                        <th scope="col">Time</th>
                        <th scope="col">Temperature</th>
                        <th scope="col">Feels Like</th>
                        <th scope="col">Wind</th>
                        <th scope="col">Humidity</th>
                        <th scope="col">Rain</th>
                    </tr>
                </thead>
                <tbody id="forecast">
                </tbody>
            </table>
        </div>
    </div>
@endsection
@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
@endpush
@push('js')
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.8.4/moment.min.js"></script>
    <script type="text/javascript"
        src="https://maps.google.com/maps/api/js?key={{ env('GOOGLE_API_KEY') }}&libraries=places">
    </script>
    <script>
        let map, marker, oldMarker
        let latitude, longitude;
        console.log(latitude, longitude);

        $(document).ready(function() {
            mapsInit();
            autoComplete();
        });

        function mapsInit() {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    latitude = position.coords.latitude;
                    longitude = position.coords.longitude;

                    getNameLocation(latitude, longitude);
                })
        }

        function getNameLocation(latitude, longitude) {
            var geocoder = new google.maps.Geocoder;
            var latlng = {
                lat: latitude,
                lng: longitude,
            };
            geocoder.geocode({
                    location: latlng
                })
                .then((response) => {
                    if (response.results[0]) {
                        let center = new google.maps.LatLng(latitude, longitude);
                        let setLocation = {
                            zoom: 16,
                            center: center,
                            mapTypeId: google.maps.MapTypeId.ROADMAP,
                        };

                        map = new google.maps.Map(document.getElementById("map"), setLocation);
                        google.maps.event.addListener(map, 'click', function(event) {
                            getNameLocation(event.latLng.lat(), event.latLng.lng());
                        });
                        addMarker(latitude, longitude, response.results[0].formatted_address)

                    } else {
                        window.alert("No results found");
                    }
                })
                .catch((e) => window.alert("Geocoder failed due to: " + e));
        }

        function getLocation(latitude = "", longitude = "", placeName = "") {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    latitude = (latitude != "" ? latitude : position.coords.latitude)
                    longitude = (longitude != "" ? longitude : position.coords.longitude)
                    console.log("latitude = " + latitude, "longitude = " + longitude, "placeName = " + placeName);

                    $.ajax({
                        url: "http://api.openweathermap.org/data/2.5/forecast",
                        method: "get",
                        dataType: "json",
                        data: {
                            appid: "{{ env('WEATHER_API_KEY') }}",
                            lat: latitude,
                            lon: longitude,
                            units: 'metric'
                        },
                        error: function(err) {
                            console.log(err);
                        },
                        success: function(res) {
                            console.log(res);
                            $('#namePlace').text('Location: ' + res.city.name);
                            $('#addressPlace').text('Address: ' + placeName);
                            $('#timePlace').text('At: ' + moment(res.list[0].dt_txt).format('DD/MM/YYYY'));
                            $('#iconWeather').attr('src', 'http://openweathermap.org/img/wn/' + res.list[0]
                                .weather[0].icon + '@2x.png');
                            $('#temperature').text(res.list[0]
                                .main.temp);
                            $('#feel').text('Feels like ' + res.list[0]
                                .main.feels_like + ' °C ' + res.list[0].weather[0].description);
                            $('#description').text();
                            $('#wind').text(res.list[0].wind.speed + ' m/s');
                            $('#humidity').text(res.list[0].main.humidity + '%');
                            $('#rain').text(res.list[0].rain['3h'] + ' mm');

                            let forecast = ``
                            res.list.forEach((item, index) => {
                                forecast += `
                                <tr>
                                    <td>${moment(item.dt_txt).format('DD/MM/YYYY')}</td>
                                    <td>${moment(item.dt_txt).format('HH:mm')}</td>
                                    <td>${item.weather[0].description}</td>
                                    <td>${item.main.temp} °C</td>
                                    <td>${item.main.feels_like} °C</td>
                                    <td>${item.main.humidity} %</td>
                                    <td>${item.wind.speed} m/s</td>
                                    `
                                item.hasOwnProperty('rain') ? `<td>${item.rain['3h']} mm</td>` : `<td>-</td>
                                </tr>
                                `
                            })
                            $("#forecast").html(forecast)
                        }
                    });
                })
        }

        function addMarker(latitude, longitude, placeName = "") {
            if (oldMarker) {
                oldMarker.setMap(null)
            }

            let icon = {
                url: "{{ url('/assets/img/marker.png') }}",
                scaledSize: new google.maps.Size(50, 50),
                origin: new google.maps.Point(0, 0),
                anchor: new google.maps.Point(25, 45),
            };

            marker = new google.maps.Marker({
                position: new google.maps.LatLng(latitude, longitude),
                map: map,
                icon: icon,
                draggable: true
            });

            oldMarker = marker
            getLocation(latitude, longitude, placeName)
        }

        function autoComplete() {
            var input = document.getElementById('search');
            var autocomplete = new google.maps.places.Autocomplete(input);

            autocomplete.addListener('place_changed', function() {
                var place = autocomplete.getPlace();
                latitude = place.geometry.location.lat();
                longitude = place.geometry.location.lng();
                console.log(latitude, longitude, place.formatted_address);
                let center = new google.maps.LatLng(latitude, longitude);
                let setLocation = {
                    zoom: 16,
                    center: center,
                    mapTypeId: google.maps.MapTypeId.ROADMAP,
                };
                map = new google.maps.Map(document.getElementById("map"), setLocation);
                getNameLocation(latitude, longitude);
            });
        }
    </script>
@endpush
