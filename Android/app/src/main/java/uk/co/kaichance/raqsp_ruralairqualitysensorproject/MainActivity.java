package uk.co.kaichance.raqsp_ruralairqualitysensorproject;

import android.Manifest;
import android.content.Context;
import android.content.Intent;
import android.content.pm.PackageManager;
import android.location.Location;
import android.location.LocationManager;
import android.support.v4.app.ActivityCompat;
import android.support.v4.content.ContextCompat;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;

import android.view.View;
import android.widget.TextView;
import android.widget.Toast;

import com.android.volley.Request;
import com.android.volley.Response;
import com.android.volley.VolleyError;
import com.android.volley.toolbox.JsonObjectRequest;
import com.android.volley.toolbox.Volley;
import com.google.android.gms.maps.CameraUpdateFactory;
import com.google.android.gms.maps.CameraUpdate;
import com.google.android.gms.maps.GoogleMap;
import com.google.android.gms.maps.OnMapReadyCallback;
import com.google.android.gms.maps.SupportMapFragment;
import com.google.android.gms.maps.model.LatLng;
import com.google.android.gms.maps.model.Marker;
import com.google.android.gms.maps.model.MarkerOptions;


import com.google.android.gms.maps.SupportMapFragment;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.HashMap;

public class MainActivity extends AppCompatActivity implements OnMapReadyCallback{
    //Setup
    private GoogleMap mMap;
    private static final int PERMISSIONS_REQUEST_ACCESS_FINE_LOC = 1;
    String serverAddress = "http://127.0.0.1"; //PLACE SERVER ADDRESS HERE
    JSONArray devices; //init
    Marker selectedMarker;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);

        //Get the supporting elements for the mapping fragment
        SupportMapFragment mapFragment = (SupportMapFragment)getSupportFragmentManager().findFragmentById(R.id.mapMain);
        mapFragment.getMapAsync(this);

        //Check for location permissions
        if (ContextCompat.checkSelfPermission(this, Manifest.permission.ACCESS_FINE_LOCATION) != PackageManager.PERMISSION_GRANTED)
        {
            //Request Permission
            ActivityCompat.requestPermissions(this,
                    new String[]{Manifest.permission.ACCESS_FINE_LOCATION},
                    PERMISSIONS_REQUEST_ACCESS_FINE_LOC);
        }

    }

    //SRC: http://gregoryhasseler.com/2017/02/20/requesting-dangerous-permissions-on-android.html
    //Local permissions
    @Override
    public void onRequestPermissionsResult(int requestCode, String permissions[], int[] grantResults)
    {
        if (permissions.length == 0)
        {
            //Nothing granted - request avoided
            return;
        }
        switch (requestCode) {
            case PERMISSIONS_REQUEST_ACCESS_FINE_LOC:

                for (int i = 0; i < permissions.length; i++)
                {
                    switch(permissions[i])
                    {
                        case Manifest.permission.ACCESS_FINE_LOCATION:
                            if (grantResults[i] == PackageManager.PERMISSION_GRANTED)
                            {
                                //permission granted
                            }
                            else{
                                //none
                            }
                    }
                }
        }

    }
    //Loads once the async process for loading mapping data has completed
    @Override
    public void onMapReady(GoogleMap googleMap) {
        mMap = googleMap; //set global map reference, for future use as markers etc
        mMap.getUiSettings().setMapToolbarEnabled(false); //Remove buttons for navigation and clickout to maps application - these are not necessary
        getDeviceListJSON(serverAddress); //Retrieve data around sensing hardware
        setMapCameraLocation(); //Set the location of the camera to currently sensed location using GPS
    }

    //This function takes the recieved JSON data, and parses it to the locations on the map
    public void populateMapJSON(JSONArray data, GoogleMap map)
    {
        LatLng deviceMarkers; //reusable variable containing location data
        try
        {   //Iterate through the returned data, parsing and then setting marker data
            for (int i = 0; i < data.length(); i++)
            {
                try
                {
                    JSONObject tempObj = data.getJSONObject(i); //select entry i
                    String devID = tempObj.getString("DEVICEID"); //retrieve data
                    String loc_LATI = tempObj.getString("loc_LATI");
                    String loc_LONG = tempObj.getString("loc_LONG");
                    deviceMarkers = new LatLng(Double.parseDouble(loc_LATI), Double.parseDouble(loc_LONG)); //explicit type conversion
                    MarkerOptions mkop = new MarkerOptions().position(deviceMarkers).title("Device: " + devID); //set marker properties
                    mMap.addMarker(mkop); //then add to the map
                }
                catch (Exception e)
                {
                    //error handle
                    e.printStackTrace();
                    Toast.makeText(this, "Error populating maps", Toast.LENGTH_LONG).show(); //inform user of errors
                }

            }
            //Waits until the marker is clicked, and returns data for a selected marker to a predefined location
            mMap.setOnMarkerClickListener(new GoogleMap.OnMarkerClickListener() {
                @Override
                public boolean onMarkerClick(Marker marker) {
                    TextView txt = (TextView)findViewById(R.id.markerTxt);
                    selectedMarker = marker;
                    txt.setText("Selected " + marker.getTitle());
                    return false;
                }
            });
        }
        catch (Exception e)
        {
            e.printStackTrace();
            //Errors already handled by this point, print trace data as a failsafe
        }
    }


    //Returns an accessible array with device specific data - requires the server ip/domain supplied eg "http://127.0.0.1"
    void getDeviceListJSON(String urlbase)
    {
        String url = urlbase + "/api/app/getDevices.php";
        JsonObjectRequest jsonRequest = new JsonObjectRequest(Request.Method.GET, url, null, new Response.Listener<JSONObject>() {
            @Override
            public void onResponse(JSONObject response) {
                //parse the json data
                try
                {
                    devices = response.getJSONArray("records"); //extract only the data we require
                    populateMapJSON(devices,mMap); //load objects onto the map
                }
                catch (JSONException e)
                {
                    e.printStackTrace();
                }
            }
        }, new Response.ErrorListener() {
            @Override
            public void onErrorResponse(VolleyError error) {
                error.printStackTrace();
            }
        });
        Volley.newRequestQueue(this).add(jsonRequest); //adds to queue (Volley uses async processes and loads data when it sees fit
    }

    //Code to set current mapping location - simple
    void setMapCameraLocation()
    {
        try{
            if (ContextCompat.checkSelfPermission(this.getApplicationContext(), Manifest.permission.ACCESS_FINE_LOCATION) == PackageManager.PERMISSION_GRANTED)
            {
                mMap.setMyLocationEnabled(true);
                mMap.getUiSettings().setMyLocationButtonEnabled(true);
                //Then get location
                LocationManager locMan = (LocationManager) this.getSystemService(Context.LOCATION_SERVICE);
                Location lkl = locMan.getLastKnownLocation(LocationManager.GPS_PROVIDER);
                double lat = 0; //init
                double longi = 0;
                if (lkl == null)
                {
                    //no processing necessary
                }
                else
                {
                    lat = lkl.getLatitude();
                    longi = lkl.getLongitude();
                    //mMap.moveCamera(CameraUpdateFactory.newLatLngZoom(new LatLng(lat,longi),1));
                    CameraUpdate cuf = CameraUpdateFactory.newLatLngZoom(new LatLng(lat,longi),13);
                    mMap.animateCamera(cuf);
                }
            }
            else
            {
                mMap.setMyLocationEnabled(false); //cover all bases
                mMap.getUiSettings().setMyLocationButtonEnabled(false);
                Toast.makeText(this, "Location Services Not Enabled - Please Locate Map As Required", Toast.LENGTH_LONG).show();
            }

        }
        catch (Exception e){
            e.printStackTrace(); //failsafe - errors handled
        }

    }

    //When the button is clicked, passes parameters to the next window or informs the use of the error which has occured (no selection)
    public void createReadingsActivity(View view)
    {
        try
        {
            Intent displayReadings = new Intent(this,DisplayReadings.class);
            displayReadings.putExtra("devID", selectedMarker.getTitle().substring(8)); //8
            displayReadings.putExtra("serverAddr", serverAddress);
            startActivity(displayReadings);
        }
        catch (Exception e)
        {
            Toast.makeText(this, "Please Select A Device to Continue", Toast.LENGTH_LONG).show();
        }

    }
}
