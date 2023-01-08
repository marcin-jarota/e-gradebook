import React from 'react'

const Select = ({ options, onChange, label, required }) => {
    return (
        <div className="form-group">
            <label htmlFor="classGroup">{label} {required ? <span className="text-danger">*</span> : null}</label>
            <select className="form-control" required={required} id="classGroup" onChange={(e) => onChange(Number(e.target.value))}>
                <option disabled selected>-</option>
                {options.map((option) => <option key={option.id} value={option.id}>{option.name}</option>)}
            </select>
        </div>
    )
}

export const SelectInput = React.memo(Select);