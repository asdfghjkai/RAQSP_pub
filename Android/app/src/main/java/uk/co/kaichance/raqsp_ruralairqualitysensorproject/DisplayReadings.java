package uk.co.kaichance.raqsp_ruralairqualitysensorproject;

import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.widget.ListAdapter;
import android.widget.ListView;
import android.widget.SimpleAdapter;
import android.widget.TextView;
import android.widget.Toast;


import com.android.volley.Request;
import com.android.volley.Response;
import com.android.volley.VolleyError;
import com.android.volley.toolbox.JsonObjectRequest;
import com.android.volley.toolbox.Volley;


import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;

public class DisplayReadings extends AppCompatActivity {

    private ListView lv;
    private String deviceID, serverAddress;
    private JSONArray data;
    ArrayList<HashMap<String, String>> dataList;
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        //init
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_display_readings);
        //get params
        deviceID = getIntent().getExtras().getString("devID");
        serverAddress = getIntent().getExtras().getString("serverAddr");
        //Set Visual Cues
        TextView device = (TextView)findViewById(R.id.txtDeviceID);
        device.setText("Device " + deviceID);
        //Setup data stores
        dataList = new ArrayList<>();
        lv = (ListView) findViewById(R.id.dataList);
        //Retrieve data from server
        updateJSONList(serverAddress,deviceID, "100");
    }



    //Add data to the listview using a simple adapter - data parsed from web service
    void inflateView()
    {
        ListAdapter listAdap = new SimpleAdapter(DisplayReadings.this, dataList, R.layout.sensevaluelayout, new String[] {"Sensor","DateTime","Value"}, new int[] {R.id.listtxtSensor,R.id.listtxtDateTime, R.id.listtxtValue} );
        lv.setAdapter(listAdap);
    }

    //Returns an accessible array with device specific data - requires the server ip/domain supplied eg "http://127.0.0.1"
    void updateJSONList(String urlbase, String devID, String limit)
    {
        String url = urlbase + "/api/app/retrieveData.php?devID=" + devID + "&limit=" + limit; //string builder
        JsonObjectRequest jsonRequest = new JsonObjectRequest(Request.Method.GET, url, null, new Response.Listener<JSONObject>() {
            @Override
            public void onResponse(JSONObject response) {
                //parse the json data
                try
                {
                    data = response.getJSONArray("records"); //extract only the data we require from the records array
                    for (int i = 0; i < data.length(); i++)
                    {
                        try
                        {   //get vals
                            JSONObject tempObj = data.getJSONObject(i); //iterate through each object, selecting properties from each dataset
                            String SENSOR_NAME = tempObj.getString("SENSOR_NAME");
                            String DATETIME = tempObj.getString("DATETIME");
                            String VALUE = tempObj.getString("VALUE");
                            String UNIT = tempObj.getString("UNIT");
                            HashMap<String, String> dataitem = new HashMap<>();
                            dataitem.put("Sensor", SENSOR_NAME);
                            dataitem.put("DateTime",DATETIME);
                            dataitem.put("Value",VALUE + " " + UNIT);
                            dataList.add(dataitem);

                        }
                        catch (JSONException e)
                        {
                            //error handle
                            e.printStackTrace();
                            Toast.makeText(getApplicationContext(), "Error populating data", Toast.LENGTH_LONG).show(); //show errors to the user if it cannot load the function

                        }

                    }
                    inflateView(); //populate
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

        //SUBMIT
        Volley.newRequestQueue(this).add(jsonRequest); //submit the request to the queue
    }
}
