import React from "react";


export default function Comment(props) {
  const { id, name, message, time, parent_id } = props.comment;

  return (
    <div className={parent_id > 0 ? "media mb-3 ml-5" : "media mb-3"} >
      <img
        className="mr-3 bg-light rounded"
        width="48"
        height="48"
        src={`https://api.adorable.io/avatars/48/${name.toLowerCase()}@adorable.io.png`}
        alt={name}
      />

      <div className="media-body p-2 shadow-sm rounded bg-light border">
        <small className="float-right text-muted">#{id} ({time})</small>
        <h6 className="mt-0 mb-1 text-muted">{name}</h6>
        {message}
        <form>
            <button className ="btn btn-primary float-right text-white">💬 Comment</button>
        </form>
      </div>
    </div>
  );
}
