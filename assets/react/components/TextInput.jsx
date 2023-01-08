import React from 'react';

const Input = (props) => {
    const { required, id, label, onChange, ...restProps } = props
    return (
        <div className="col form-group">
            <label htmlFor={id}>{label} {required ? <span className="text-danger">*</span> : null}</label>
            <input
                className="form-control"
                onChange={(e) => onChange({ value: e.target.value })} 
                id={id}
                {...restProps}>
            </input>
        </div>
    )
}

export const TextInput = React.memo(Input)