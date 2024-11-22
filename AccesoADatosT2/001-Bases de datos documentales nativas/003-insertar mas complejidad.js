db.clientes.insertOne(
	{
		nombre:"Javier",
		apellidos:"Hortigüela Valiente",
		correos:[
			{	
				tipo:'personal',
				correo:'info@horti.com'
			},{	
				tipo:'trabajo',
				correo:'info@horti.com'
			}]
	}
)
