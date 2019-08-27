import React from "react";

/*constructor(props) {
    this.onSubmit = this.onSubmit.bind(this);
}*/
/*
onSubmit(e) {
    fetch("http://localhost", {
        method: "delete",
        body: JSON.stringify(id)
    })
        .then(res => res.json())
        .then(res => {
            if (res.error) {
                this.setState({loading: false, error: res.error});
            } else {

            }
        })
        .catch(err => {
            this.setState({
                error: "Something went wrong while deleting comment.",
                loading: false
            });
        });
}*/

export default function Comment(props) {
    const {id, name, message, time, parent_id} = props.comment;

    return (
        <div className={parent_id > 0 ? "media mb-3 ml-5" : "media mb-3"}>
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
                <form className="form-group" >
                    <button className="btn btn-primary float-right text-white ml-3">❌ Delete</button>
                    <button className="btn btn-primary float-right text-white ml-3">✏ Edit</button>
                    <button className="btn btn-primary float-right text-white ml-3">💬 Comment</button>
                </form>
            </div>
        </div>
    );
}