<!DOCTYPE html>
<html>
  <head> 
    @include('admin.css')


    <style type="text/css">
        .table_deg
    {
        border: 2px solid white;
        margin: auto;
        width: 40%;
        text-align: center;
        margin-top: 40px;
    }

    .th_deg
    {
        background-color: skyblue;
        padding: 10px;
    }

    tr{
        border:  3px solid white;
    }
    td{
        border:  10px;
    }
    
    </style>
  </head>
  <body>
    @include('admin.header')
<!-- Sidebar Navigation-->
    @include('admin.sidebar')
<!-- Sidebar Navigation end-->
      
<div class="page-content">
    <div class="page-header">
      <div class="container-fluid">



        <table class="table_deg">

            <tr>
                <th class="th_deg">Room_id</th>
                <th class="th_deg">Customer name</th>
                <th class="th_deg">Email</th>
                <th class="th_deg">Phone</th>
                <th class="th_deg">Arrival Date</th>
                <th class="th_deg">Leaving Date</th>
                <th class="th_deg">Status</th>
                <th class="th_deg">Room Title</th>
                <th class="th_deg">Price</th>
                <th class="th_deg">Image</th>
                <th class="th_deg">Delete</th>
                <th class="th_deg">Status Update</th>
            </tr>


            @foreach ($data as $data)
                
            <tr>
                <td>{{ $data->room_id }}</td>
                <td>{{ $data->name }}</td>
                <td>{{ $data->email }}</td>
                <td>{{ $data->phone }}</td>
                <td>{{ $data->start_date }}</td>
                <td>{{ $data->end_date }}</td>
                <td>
                    @if ($data->status == 'ACC BOSSKU')
                    <span style="color: skyblue;">ACC BOSSKU</span>  
                    @endif

                    @if ($data->status == 'YAH KE REJECT')
                    <span style="color: red;">YAH KE REJECT</span>  
                    @endif

                    @if ($data->status == 'waiting')
                    <span style="color: green;">waiting</span>  
                    @endif



                </td>
                <td>{{ $data->room->room_title }}</td>
                <td>{{ $data->room->price }}</td>
                <td>
                    <img style="width: 100!important" src="/room/{{ $data->room->image }}">
                </td>
                <td> 
                    <a onclick="return confirm ('Kamu yakin mau hapus?')" class="btn btn-danger" href="{{ url('delete_booking', $data->id) }}">Delete</a>
                </td>

                <td>
                    <span style="padding-bottom: 10px"> 
                    <a class="btn btn-success" href="{{ url('approve_book',$data->id)}}">Approve </a>
                    </span>
                    
                    <span> 
                    <a class="btn btn-warning" href="{{ url('reject_book',$data->id)}}">Rejected </a> 
                    </span>
                </td>
        
            </tr>
            
            @endforeach

        </table>




      </div>
    </div>
</div>


    @include('admin.footer')


  </body>
</html>